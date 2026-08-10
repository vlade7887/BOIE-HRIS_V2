<?php

namespace App\Http\Controllers;

use App\Models\ApprovalAuditLog;
use Illuminate\View\View;

class ApprovalAuditLogController extends Controller
{
    public function index(): View
    {
        $approvalAuditLogs = ApprovalAuditLog::query()
            ->with([
                'actorUser',
                'actorEmployee',
            ])
            ->latest('occurred_at')
            ->paginate(25);

        return view(
            'approval-audit-logs.index',
            compact('approvalAuditLogs')
        );
    }

    public function show(
        ApprovalAuditLog $approvalAuditLog
    ): View {
        $approvalAuditLog->load([
            'actorUser',
            'actorEmployee',
        ]);

        return view(
            'approval-audit-logs.show',
            compact('approvalAuditLog')
        );
    }
}