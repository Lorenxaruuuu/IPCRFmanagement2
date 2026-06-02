<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        ?int   $userId     = null,
        string $entityType = '',
        ?int   $entityId   = null,
        array  $details    = []
    ): void {
        AuditLog::create([
            'user_id'     => $userId ?? (session('user')['id'] ?? null),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
            'ip_address'  => Request::ip(),
        ]);
    }
}
