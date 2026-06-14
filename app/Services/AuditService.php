<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    /**
     * Registra un evento de auditoría (RN-05/RF-010).
     * Captura automáticamente el usuario autenticado y la IP.
     */
    public function log(string $action, ?string $description = null, ?int $userId = null): void
    {
        AuditLog::create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
        ]);
    }
}
