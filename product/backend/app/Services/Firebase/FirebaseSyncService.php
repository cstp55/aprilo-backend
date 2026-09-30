<?php

namespace App\Services\Firebase;

use App\Models\Organization;
use App\Models\OrganizationChatUsage;
use App\Models\OrganizationSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FirebaseSyncService
{
    protected string $projectId;
    protected string $apiKey;
    protected string $firestoreBaseUrl;
    protected string $rtdbBaseUrl;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id', env('FIREBASE_PROJECT_ID', 'aprilo-infotech'));
        $this->apiKey = config('services.firebase.api_key', env('FIREBASE_API_KEY', 'AIzaSyDU5Ce2X5w35sZH81e5nX9i41xXj6YXoMg'));
        $this->firestoreBaseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
        $this->rtdbBaseUrl = env('FIREBASE_DATABASE_URL', 'https://aprilo-infotech-default-rtdb.firebaseio.com');
    }

    /**
     * Fetch all documents from a Firestore collection.
     */
    public function fetchCollection(string $collectionName): array
    {
        $allDocs = [];
        $pageToken = null;
        $url = "{$this->firestoreBaseUrl}/{$collectionName}?pageSize=300&key={$this->apiKey}";

        do {
            $requestUrl = $pageToken ? ($url . '&pageToken=' . urlencode($pageToken)) : $url;
            $response = Http::withoutVerifying()->timeout(15)->get($requestUrl);

            if (! $response->successful()) {
                Log::warning("FirebaseSyncService: Failed to fetch {$collectionName}", [
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
                break;
            }

            $data = $response->json();
            $documents = $data['documents'] ?? [];
            foreach ($documents as $doc) {
                $allDocs[] = $this->parseFirestoreDocument($doc);
            }

            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken);

        return $allDocs;
    }

    /**
     * Parse raw Firestore REST document structure into flat key-value pairs.
     */
    public function parseFirestoreDocument(array $rawDoc): array
    {
        $fields = $rawDoc['fields'] ?? [];
        $parsed = [
            '_firestore_name' => $rawDoc['name'] ?? '',
            '_create_time' => $rawDoc['createTime'] ?? null,
            '_update_time' => $rawDoc['updateTime'] ?? null,
        ];

        foreach ($fields as $key => $val) {
            if (isset($val['stringValue'])) {
                $parsed[$key] = $val['stringValue'];
            } elseif (isset($val['integerValue'])) {
                $parsed[$key] = (int) $val['integerValue'];
            } elseif (isset($val['doubleValue'])) {
                $parsed[$key] = (float) $val['doubleValue'];
            } elseif (isset($val['booleanValue'])) {
                $parsed[$key] = (bool) $val['booleanValue'];
            } elseif (isset($val['timestampValue'])) {
                $parsed[$key] = $val['timestampValue'];
            } elseif (isset($val['nullValue'])) {
                $parsed[$key] = null;
            } elseif (isset($val['arrayValue']['values'])) {
                $parsed[$key] = array_map(fn ($item) => reset($item), $val['arrayValue']['values']);
            } else {
                $parsed[$key] = $val;
            }
        }

        if (! isset($parsed['id']) && isset($rawDoc['name'])) {
            $parsed['id'] = basename($rawDoc['name']);
        }

        return $parsed;
    }

    /**
     * Get aggregated metrics from all Firestore collections for monitoring board.
     */
    public function getFirestoreSummary(?string $filterOrgId = null): array
    {
        $conversations = $this->fetchCollection('conversations');
        $customers = $this->fetchCollection('customers');
        $messages = $this->fetchCollection('messages');
        $leads = $this->fetchCollection('leads');
        
        $bookings = $this->fetchCollection('bookings');
        if (empty($bookings)) {
            $bookings = $this->fetchCollection('bookins');
        }

        $mail = $this->fetchCollection('mail');
        if (empty($mail)) {
            $mail = $this->fetchCollection('mails');
        }
        if (empty($mail)) {
            $mail = $this->fetchCollection('emails');
        }

        if ($filterOrgId) {
            $conversations = array_values(array_filter(
                $conversations,
                fn ($c) => ($c['organization_id'] ?? '') === $filterOrgId
            ));

            $convIds = array_column($conversations, 'id');

            $customers = array_values(array_filter(
                $customers,
                fn ($c) => ($c['organization_id'] ?? '') === $filterOrgId
            ));

            $messages = array_values(array_filter(
                $messages,
                function ($m) use ($filterOrgId, $convIds) {
                    $org = $m['organization_id'] ?? null;
                    if ($org === $filterOrgId) {
                        return true;
                    }
                    if (! empty($m['conversation_id']) && in_array($m['conversation_id'], $convIds, true)) {
                        return true;
                    }
                    // If no explicit org_id on message but it's part of the aprilo tenant's data
                    return ! $org && $filterOrgId === '01a0ca0a-90e0-7257-a23c-4d5c723aff99';
                }
            ));

            $leads = array_values(array_filter(
                $leads,
                fn ($l) => ($l['organization_id'] ?? '') === $filterOrgId || ! isset($l['organization_id'])
            ));

            $bookings = array_values(array_filter(
                $bookings,
                fn ($b) => ($b['organization_id'] ?? '') === $filterOrgId || ! isset($b['organization_id'])
            ));

            $mail = array_values(array_filter(
                $mail,
                fn ($m) => ($m['organization_id'] ?? '') === $filterOrgId || ! isset($m['organization_id'])
            ));
        }

        $totalConversations = count($conversations);
        $totalCustomers = count($customers);
        $totalMessages = count($messages);
        $totalLeads = count($leads);
        $totalBookings = count($bookings);
        $totalMail = count($mail);

        $totalAiQueries = 0;
        $totalAiResolved = 0;
        $totalHumanPending = 0;
        $totalHumanConnected = 0;
        $totalWaitSeconds = 0;
        $waitCount = 0;
        $channels = [];
        $statusCounts = [];
        $modes = ['ai' => 0, 'human_pending' => 0, 'human' => 0];

        foreach ($conversations as $c) {
            $queries = (int) ($c['ai_query_count'] ?? 0);
            if ($queries === 0 && ($c['mode'] ?? '') === 'ai') {
                $queries = 1;
            }
            $totalAiQueries += $queries;

            if (! empty($c['ai_resolved'])) {
                $totalAiResolved++;
            }

            $mode = $c['mode'] ?? 'ai';
            $modes[$mode] = ($modes[$mode] ?? 0) + 1;
            if ($mode === 'human_pending') {
                $totalHumanPending++;
            } elseif ($mode === 'human') {
                $totalHumanConnected++;
            }

            $ch = $c['channel'] ?? 'website';
            $channels[$ch] = ($channels[$ch] ?? 0) + 1;

            $st = $c['status'] ?? 'open';
            $statusCounts[$st] = ($statusCounts[$st] ?? 0) + 1;

            if (isset($c['wait_time_seconds']) && is_numeric($c['wait_time_seconds'])) {
                $totalWaitSeconds += (int) $c['wait_time_seconds'];
                $waitCount++;
            }
        }

        $avgWaitTime = $waitCount > 0 ? round($totalWaitSeconds / $waitCount, 1) : 0;

        return [
            'total_conversations' => $totalConversations,
            'total_customers' => $totalCustomers,
            'total_messages' => $totalMessages,
            'total_leads' => $totalLeads,
            'total_bookings' => $totalBookings,
            'total_mail' => $totalMail,
            'collections_counts' => [
                'conversations' => $totalConversations,
                'messages' => $totalMessages,
                'customers' => $totalCustomers,
                'leads' => $totalLeads,
                'bookings' => $totalBookings,
                'mail' => $totalMail,
            ],
            'total_ai_queries' => $totalAiQueries,
            'total_ai_resolved' => $totalAiResolved,
            'total_human_pending' => $totalHumanPending,
            'total_human_connected' => $totalHumanConnected,
            'avg_wait_time_seconds' => $avgWaitTime,
            'channels' => $channels,
            'status_counts' => $statusCounts,
            'modes' => $modes,
            'conversations' => array_values($conversations),
            'customers' => array_values($customers),
            'messages' => array_values($messages),
            'leads' => array_values($leads),
            'bookings' => array_values($bookings),
            'mail' => array_values($mail),
        ];
    }

    /**
     * Sync conversations from Firestore into local MySQL database (organization_chat_usages)
     * and bridge active state into Firebase Realtime Database for the agent app.
     *
     * @param bool $force Bypass cache and re-sync
     * @return array Summary of synced records
     */
    public function syncAll(bool $force = false): array
    {
        $cacheKey = 'firebase_chat_synced_data';
        if (! $force && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $conversations = $this->fetchCollection('conversations');
        $customers = $this->fetchCollection('customers');
        $messages = $this->fetchCollection('messages');
        $leads = $this->fetchCollection('leads');
        
        $bookings = $this->fetchCollection('bookings');
        if (empty($bookings)) {
            $bookings = $this->fetchCollection('bookins');
        }

        $mail = $this->fetchCollection('mail');
        if (empty($mail)) {
            $mail = $this->fetchCollection('mails');
        }

        $syncedDays = 0;
        $totalConvs = count($conversations);
        $totalAiQueries = 0;
        $totalTokens = 0;

        // Group conversations by organization_id and date
        $grouped = [];
        $knownOrgIds = Organization::pluck('id')->all();
        $fallbackOrgId = $knownOrgIds[0] ?? '01a0ca0a-90e0-7257-a23c-4d5c723aff99';

        foreach ($conversations as $conv) {
            $orgId = $conv['organization_id'] ?? null;

            if (! $orgId || ! in_array($orgId, $knownOrgIds, true)) {
                $orgId = $fallbackOrgId;
            }

            if (! $orgId) {
                continue;
            }

            $rawDate = $conv['created_at'] ?? $conv['session_started_at'] ?? $conv['_create_time'] ?? now()->toDateString();
            try {
                $date = Carbon::parse($rawDate)->toDateString();
            } catch (\Exception $e) {
                $date = Carbon::today()->toDateString();
            }

            $aiQueries = max((int) ($conv['ai_query_count'] ?? 0), 0);
            if ($aiQueries === 0 && ($conv['mode'] ?? '') === 'ai') {
                $aiQueries = 1;
            }

            $promptTokens = $aiQueries * 350;
            $completionTokens = $aiQueries * 450;
            $tokens = $promptTokens + $completionTokens;
            if ($tokens === 0) {
                $tokens = 200;
                $promptTokens = 100;
                $completionTokens = 100;
            }

            $key = "{$orgId}_{$date}";
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'organization_id' => $orgId,
                    'usage_date' => $date,
                    'conversations_count' => 0,
                    'queries_count' => 0,
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                    'cost_estimate' => 0.0,
                ];
            }

            $grouped[$key]['conversations_count'] += 1;
            $grouped[$key]['queries_count'] += max(1, $aiQueries);
            $grouped[$key]['prompt_tokens'] += $promptTokens;
            $grouped[$key]['completion_tokens'] += $completionTokens;
            $grouped[$key]['total_tokens'] += $tokens;
            $grouped[$key]['cost_estimate'] += round(($tokens / 1000) * 0.0005, 4);

            $totalAiQueries += $aiQueries;
            $totalTokens += $tokens;
        }

        // Upsert into organization_chat_usages
        foreach ($grouped as $entry) {
            OrganizationChatUsage::updateOrCreate(
                [
                    'organization_id' => $entry['organization_id'],
                    'usage_date' => $entry['usage_date'],
                ],
                [
                    'queries_count' => $entry['queries_count'],
                    'prompt_tokens' => $entry['prompt_tokens'],
                    'completion_tokens' => $entry['completion_tokens'],
                    'total_tokens' => $entry['total_tokens'],
                    'cost_estimate' => $entry['cost_estimate'],
                ]
            );
            $syncedDays++;
        }

        // Update total counters on OrganizationSettings
        $orgTotals = OrganizationChatUsage::query()
            ->selectRaw('organization_id, SUM(queries_count) as total_q, SUM(total_tokens) as total_tok, SUM(cost_estimate) as total_cost')
            ->groupBy('organization_id')
            ->get();

        foreach ($orgTotals as $row) {
            $settings = OrganizationSetting::firstOrCreate(['organization_id' => $row->organization_id]);
            $update = [
                'usage_queries_count' => (int) $row->total_q,
            ];
            if (Schema::hasColumn('organization_settings', 'total_tokens_used')) {
                $update['total_tokens_used'] = (int) $row->total_tok;
            }
            if (Schema::hasColumn('organization_settings', 'usage_amount_due')) {
                $update['usage_amount_due'] = (float) $row->total_cost;
            }
            $settings->update($update);
        }

        // Bridge Firestore conversations to Firebase Realtime Database for aprilo-agent
        $rtdbSynced = $this->syncToRealtimeDatabase($conversations, $messages);

        $result = [
            'total_conversations' => $totalConvs,
            'total_customers' => count($customers),
            'total_messages' => count($messages),
            'total_leads' => count($leads),
            'total_bookings' => count($bookings),
            'total_mail' => count($mail),
            'total_ai_queries' => $totalAiQueries,
            'total_tokens' => $totalTokens,
            'synced_daily_records' => $syncedDays,
            'rtdb_synced' => $rtdbSynced,
            'last_synced_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $result, 60);

        return $result;
    }

    /**
     * Bridge Firestore conversations to Realtime Database so desktop/native agent receives them live.
     */
    public function syncToRealtimeDatabase(array $conversations, array $messages = []): bool
    {
        if (empty($this->rtdbBaseUrl)) {
            return false;
        }

        try {
            $groupedConvs = [];
            foreach ($conversations as $conv) {
                $orgId = $conv['organization_id'] ?? '01a0ca0a-90e0-7257-a23c-4d5c723aff99';
                $cid = $conv['id'] ?? null;
                if (! $cid) {
                    continue;
                }

                $groupedConvs[$orgId][$cid] = [
                    'id' => $cid,
                    'conversation_id' => $cid,
                    'organization_id' => $orgId,
                    'customer_id' => $conv['customer_id'] ?? 'cust_unknown',
                    'customer_name' => $conv['customer_name'] ?? 'Website Visitor',
                    'channel' => $conv['channel'] ?? 'website',
                    'mode' => $conv['mode'] ?? 'ai',
                    'status' => $conv['status'] ?? 'open',
                    'page_title' => $conv['page_title'] ?? '',
                    'page_url' => $conv['page_url'] ?? '',
                    'assigned_agent_id' => $conv['assigned_agent_id'] ?? null,
                    'assigned_agent_name' => $conv['assigned_agent_name'] ?? null,
                    'last_message_preview' => $conv['last_message_preview'] ?? '',
                    'created_at' => $conv['created_at'] ?? $conv['session_started_at'] ?? now()->toIso8601String(),
                    'last_message_at' => $conv['last_message_at'] ?? now()->toIso8601String(),
                    'human_requested_at' => $conv['human_requested_at'] ?? null,
                    'ai_resolved' => (bool) ($conv['ai_resolved'] ?? false),
                    'ai_query_count' => (int) ($conv['ai_query_count'] ?? 0),
                    'wait_time_seconds' => (int) ($conv['wait_time_seconds'] ?? 0),
                    'is_typing' => false,
                ];
            }

            foreach ($groupedConvs as $orgId => $convList) {
                // Write to primary tenant UUID
                Http::withoutVerifying()->timeout(10)->patch(
                    "{$this->rtdbBaseUrl}/tenants/{$orgId}/conversations.json",
                    $convList
                );

                // Also write to org_aprilo_infotech alias for agent app compatibility
                if ($orgId === '01a0ca0a-90e0-7257-a23c-4d5c723aff99') {
                    Http::withoutVerifying()->timeout(10)->patch(
                        "{$this->rtdbBaseUrl}/tenants/org_aprilo_infotech/conversations.json",
                        $convList
                    );
                }

                // If any conversation is human_pending, push human requested alert event
                foreach ($convList as $c) {
                    if (($c['mode'] ?? '') === 'human_pending' || ($c['status'] ?? '') === 'waiting') {
                        $eventId = 'evt_' . preg_replace('/[^a-zA-Z0-9_]/', '', $c['id']);
                        $eventData = [
                            'id' => $eventId,
                            'type' => 'HUMAN_REQUESTED',
                            'event' => 'HUMAN_REQUESTED',
                            'conversation_id' => $c['id'],
                            'customer_name' => $c['customer_name'] ?? 'Website Visitor',
                            'status' => 'pending',
                            'created_at' => $c['human_requested_at'] ?? now()->toIso8601String(),
                        ];
                        Http::withoutVerifying()->timeout(5)->patch(
                            "{$this->rtdbBaseUrl}/tenants/{$orgId}/events/{$eventId}.json",
                            $eventData
                        );
                        if ($orgId === '01a0ca0a-90e0-7257-a23c-4d5c723aff99') {
                            Http::withoutVerifying()->timeout(5)->patch(
                                "{$this->rtdbBaseUrl}/tenants/org_aprilo_infotech/events/{$eventId}.json",
                                $eventData
                            );
                        }
                    }
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('FirebaseSyncService: Error mirroring conversations to RTDB', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

