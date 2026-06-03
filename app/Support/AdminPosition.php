<?php

namespace App\Support;

use App\Models\User;

class AdminPosition
{
    public const RPMO     = 'rpmo';
    public const POO      = 'poo';
    public const RPMO_POO = 'rpmo_poo';

    public static function resolve(?User $user): string
    {
        if (!$user) {
            return self::RPMO;
        }

        $raw = $user->getAttributes()['position'] ?? null;
        if (is_string($raw) && in_array($raw, [self::RPMO, self::POO, self::RPMO_POO, 'none'], true)) {
            return $raw === 'none' ? self::RPMO : $raw;
        }

        return self::RPMO;
    }

    public static function isPooOnly(string $position): bool
    {
        return $position === self::POO;
    }

    public static function hasPooAccess(string $position): bool
    {
        return in_array($position, [self::POO, self::RPMO_POO], true);
    }

    public static function hasRpmoAccess(string $position): bool
    {
        return in_array($position, [self::RPMO, self::RPMO_POO, 'none'], true);
    }

    public static function assignedProvince(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $user->assigned_province
            ?? $user->region
            ?? $user->office
            ?? null;
    }

    public static function scopeUsersInProvince($query, ?string $province)
    {
        if (!$province) {
            return $query;
        }

        return $query->where(function ($q) use ($province) {
            $q->where('assigned_province', $province)
                ->orWhere('region', $province)
                ->orWhere('office', 'like', '%' . $province . '%');
        });
    }
}
