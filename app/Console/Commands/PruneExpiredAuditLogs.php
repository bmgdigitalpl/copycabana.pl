<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneExpiredAuditLogs extends Command
{
    protected $signature = 'audit-logs:prune-expired';

    protected $description = 'Delete audit logs after the configured retention period';

    public function handle(): int
    {
        $deleted = AuditLog::query()
            ->where('created_at', '<', now()->subDays((int) config('privacy.audit_retention_days')))
            ->delete();

        $this->info("Deleted {$deleted} expired audit log(s).");

        return self::SUCCESS;
    }
}
