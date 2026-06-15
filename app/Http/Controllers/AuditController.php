<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15)
            ->through(fn ($log) => [
                'id'          => $log->id,
                'action'      => $log->action,
                'description' => $log->description,
                'ip_address'  => $log->ip_address,
                'created_at'  => $log->created_at->format('d/m/Y H:i:s'),
            ]);

        return Inertia::render('Audit/Index', ['logs' => $logs]);
    }
}
