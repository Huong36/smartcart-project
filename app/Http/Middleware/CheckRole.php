<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        abort_unless($request->user()?->role === $role, 403, 'Bạn không có quyền truy cập trang này.');

        return $next($request);
    }
}
