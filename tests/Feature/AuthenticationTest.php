<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_auth_forms_but_must_log_in_for_private_pages(): void
    {
        $this->get('/login')->assertOk()->assertSee('Đăng nhập');
        $this->get('/register')->assertOk()->assertSee('Tạo tài khoản');
        $this->get('/account')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_registration_normalizes_email_hashes_password_and_ignores_injected_role(): void
    {
        $this->post('/register', [
            'name' => 'Khách mới', 'email' => 'NEW@EXAMPLE.COM',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'phone' => '0901234567', 'role' => 'admin',
        ])->assertRedirect('/account');

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertEquals('customer', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertForbidden();
    }

    public function test_registration_rejects_duplicate_email_short_password_and_bad_phone(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $this->post('/register', [
            'name' => 'Khách', 'email' => 'EXISTING@EXAMPLE.COM',
            'password' => '123', 'password_confirmation' => '456', 'phone' => 'abc',
        ])->assertSessionHasErrors(['email', 'password', 'phone']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_customer_login_redirects_to_account_and_cannot_access_admin(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/account');
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertForbidden()->assertSee('Bạn không có quyền');
        $this->get('/account')->assertOk()->assertSee($user->email);
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Tổng quan cửa hàng');
    }

    public function test_login_respects_intended_page(): void
    {
        $user = User::factory()->create();
        $this->get('/account')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/account');
    }

    public function test_failed_login_does_not_authenticate_and_is_rate_limited(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
                ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không chính xác.']);
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_removes_authenticated_access(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/account')->assertRedirect('/login');
        $this->get('/logout')->assertStatus(405);
    }

    public function test_authenticated_users_are_redirected_away_from_auth_forms(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/account');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)->get('/register')->assertRedirect('/admin');
    }
}
