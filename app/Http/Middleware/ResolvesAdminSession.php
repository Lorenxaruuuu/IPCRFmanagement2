<?php

namespace App\Http\Middleware;

use App\Models\User;

trait ResolvesAdminSession
{
    protected function sessionAdmin(): ?User
    {
        $sessionUser = session('user');
        if (!$sessionUser || ($sessionUser['role'] ?? '') !== 'admin') {
            return null;
        }

        return User::find($sessionUser['id'] ?? 0);
    }
}
