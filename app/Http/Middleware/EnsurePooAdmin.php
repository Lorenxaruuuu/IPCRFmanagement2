<?php

namespace App\Http\Middleware;

use App\Support\AdminPosition;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePooAdmin
{
    use ResolvesAdminSession;

    public function handle(Request $request, Closure $next): Response
    {
        $admin = $this->sessionAdmin();

        if (!$admin) {
            return redirect()->route('login');
        }

        if (!AdminPosition::hasPooAccess($admin->adminPositionType())) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
