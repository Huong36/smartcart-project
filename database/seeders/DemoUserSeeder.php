<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach ([
            ['admin@gmail.com', 'Quản trị viên', User::ROLE_ADMIN],
            ['user@gmail.com', 'Khách hàng Demo', User::ROLE_CUSTOMER],
        ] as [$email, $name, $role]) {
            // Chạy lại seeder không đổi mật khẩu hoặc thông tin tài khoản đã tồn tại.
            if (! User::where('email', $email)->exists()) {
                $user = new User([
                    'email' => $email,
                    'name' => $name,
                    'password' => Hash::make('12345678'),
                    'phone' => '0901234567',
                    'address' => 'Thành phố Hồ Chí Minh',
                ]);
                $user->role = $role;
                $user->save();
            }
        }
    }
}
