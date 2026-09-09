<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Client;
use Illuminate\Console\Command;

class AnonymizeExpiredClients extends Command
{
    protected $signature = 'clients:anonymize-expired';

    protected $description = 'Anonymize clients whose configured retention period has ended';

    public function handle(): int
    {
        $count = 0;

        Client::query()->whereNotNull('retention_until')->where('retention_until', '<=', now())->chunkById(100, function ($clients) use (&$count): void {
            foreach ($clients as $client) {
                if ($client->anonymized_at) {
                    continue;
                }

                $client->anonymize();
                AuditLog::create([
                    'auditable_type' => Client::class,
                    'auditable_id' => $client->id,
                    'action' => 'retention_anonymized',
                ]);
                $count++;
            }
        });

        $this->info("Anonymized {$count} client(s).");

        return self::SUCCESS;
    }
}
