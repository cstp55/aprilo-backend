<?php

namespace App\Console\Commands;

use App\Services\Firebase\FirebaseSyncService;
use Illuminate\Console\Command;

class SyncFirebaseChatData extends Command
{
    protected $signature = 'firebase:sync-chat {--force : Force sync bypassing cache}';
    protected $description = 'Synchronize chat conversations, queries, and token metrics from Firestore to MySQL';

    public function handle(FirebaseSyncService $syncService): int
    {
        $this->info('Connecting to Firestore project [aprilo-infotech]...');
        $force = (bool) $this->option('force');
        $result = $syncService->syncAll($force);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Firestore Conversations', $result['total_conversations']],
                ['Total Customers / Visitors', $result['total_customers']],
                ['Total Messages', $result['total_messages']],
                ['Total Leads', $result['total_leads'] ?? 0],
                ['Total Bookings', $result['total_bookings'] ?? 0],
                ['Total Mail / Inquiries', $result['total_mail'] ?? 0],
                ['Total AI Queries', $result['total_ai_queries']],
                ['Total Estimated Tokens', number_format($result['total_tokens'])],
                ['Realtime Database Agent Sync', !empty($result['rtdb_synced']) ? '✓ Synced (tenants/01a0ca0a-90e0-7257-a23c-4d5c723aff99)' : 'Offline'],
                ['Synced Daily Usage Records', $result['synced_daily_records']],
                ['Last Synced', $result['last_synced_at']],
            ]
        );

        $this->info('Firestore chat data successfully synchronized into monitoring database.');
        return Command::SUCCESS;
    }
}
