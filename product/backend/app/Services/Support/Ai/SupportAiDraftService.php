<?php

namespace App\Services\Support\Ai;

use App\Models\KnowledgeChunk;
use App\Models\Support\SupportAiDraft;
use App\Models\Support\SupportAiDraftSource;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\User;
use App\Services\AI\AiAnswerService;
use App\Services\Knowledge\RetrievalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportAiDraftService
{
    public function __construct(
        private readonly RetrievalService $retrievalService,
        private readonly AiAnswerService $aiService,
    ) {
    }

    /**
     * Generate an AI-assisted reply draft for a support ticket.
     * Enforces tenant scoping, RAG retrieval with citations, and saves as pending_review.
     */
    public function generateDraft(SupportTicket $ticket, User $requestedBy): SupportAiDraft
    {
        abort_unless($ticket->organization_id === $requestedBy->organization_id, 403);

        // 1. Identify the trigger customer message
        $latestCustomerComment = $ticket->comments()
            ->where('is_internal', false)
            ->where('author_type', 'customer')
            ->latest('created_at')
            ->first();

        $triggerText = $latestCustomerComment ? $latestCustomerComment->body : $ticket->description;
        $triggerPreview = mb_substr($triggerText, 0, 250);

        // 2. Perform Tenant-Scoped RAG retrieval
        $retrievedItems = [];
        try {
            $retrievedItems = $this->retrievalService->retrieve($requestedBy, $triggerText, 5);
        } catch (\Throwable $e) {
            Log::warning("SupportAiDraftService: Retrieval failed for ticket {$ticket->ticket_number}: {$e->getMessage()}");
        }

        // 3. Assemble minimal context
        $contextSnippets = [];
        $sourcesToPersist = [];

        foreach ($retrievedItems as $item) {
            $chunk = $item['chunk'] ?? null;
            $source = $item['source'] ?? null;
            $score = $item['score'] ?? 0.0;

            if ($chunk instanceof KnowledgeChunk && $source) {
                $contextSnippets[] = "Source [{$source->title}]: " . $chunk->chunk_text;
                $sourcesToPersist[] = [
                    'source_id' => $source->id,
                    'chunk_id' => $chunk->id,
                    'title' => $source->title,
                    'citation_excerpt' => mb_substr($chunk->chunk_text, 0, 300),
                    'relevance_score' => $score,
                ];
            }
        }

        // 4. Generate proposed response using AI service
        $policy = [
            'role' => 'technical_support_copilot',
            'ticket_subject' => $ticket->subject,
            'ticket_number' => $ticket->ticket_number,
            'priority' => $ticket->priority,
            'department' => $ticket->department,
            'must_cite_sources' => true,
            'instruction' => 'You are drafting an internal proposed customer reply for human agent review. Provide a professional, accurate, and empathetic solution based on the retrieved knowledge sources. Never invent system credentials, fake error codes, or unsupported promises.',
        ];

        $aiResult = $this->aiService->answer($triggerText, $retrievedItems, $policy);
        $proposedReply = $aiResult['answer_text'] ?? "Thank you for reaching out. We have logged ticket {$ticket->ticket_number} and our engineering team is actively investigating the issue.";

        // 5. Store in MySQL within a database transaction
        return DB::transaction(function () use ($ticket, $requestedBy, $latestCustomerComment, $triggerPreview, $proposedReply, $sourcesToPersist, $aiResult) {
            // Mark any previous pending drafts for this ticket as superseded
            SupportAiDraft::where('ticket_id', $ticket->id)
                ->where('status', 'pending_review')
                ->update(['status' => 'superseded']);

            $draft = SupportAiDraft::create([
                'organization_id' => $ticket->organization_id,
                'ticket_id' => $ticket->id,
                'trigger_comment_id' => $latestCustomerComment?->id,
                'trigger_message_preview' => $triggerPreview,
                'proposed_reply' => $proposedReply,
                'status' => 'pending_review',
                'is_stale' => false,
                'model' => config('services.ai.default_model', 'gemini-1.5-flash'),
                'confidence_score' => $aiResult['confidence'] ?? 0.85,
                'generation_metadata' => [
                    'retrieved_source_count' => count($sourcesToPersist),
                    'requested_by_user_id' => $requestedBy->id,
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);

            // Persist source citations
            foreach ($sourcesToPersist as $sourceData) {
                SupportAiDraftSource::create([
                    'draft_id' => $draft->id,
                    'source_id' => $sourceData['source_id'],
                    'chunk_id' => $sourceData['chunk_id'],
                    'title' => $sourceData['title'],
                    'citation_excerpt' => $sourceData['citation_excerpt'],
                    'relevance_score' => $sourceData['relevance_score'],
                ]);
            }

            return $draft->fresh(['sources', 'ticket']);
        });
    }

    /**
     * Mark pending drafts as stale if a new customer message arrives.
     */
    public function markDraftsAsStale(SupportTicket $ticket): void
    {
        SupportAiDraft::where('ticket_id', $ticket->id)
            ->where('status', 'pending_review')
            ->update(['is_stale' => true]);
    }
}
