<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    private const ADMIN_ROLES = ['admin', 'hr_manager', 'hr'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login.show');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login.show')
                ->withErrors(['email' => 'Tài khoản đã bị vô hiệu hoá.']);
        }

        if (! $user->hasAnyRole(self::ADMIN_ROLES)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login.show')
                ->withErrors(['email' => 'Tài khoản không có quyền truy cập hệ thống.']);
        }

        return $next($request);
    }
}
