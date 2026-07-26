<?php

namespace App\Jobs;

use App\Models\KnowledgeSource;
use App\Services\AI\EmbeddingService;
use App\Services\Knowledge\ChunkingService;
use App\Services\Knowledge\DocumentParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessKnowledgeSource implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $sourceId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentParser $parser, ChunkingService $chunking, EmbeddingService $embeddings): void
    {
        $source = KnowledgeSource::find($this->sourceId);

        if (! $source) {
            return;
        }

        $source->update(['status' => 'indexing']);

        if (! $source->file_path || ! Storage::exists($source->file_path)) {
            $source->update(['status' => 'failed']);

            return;
        }

        try {
            $text = $parser->parse(Storage::path($source->file_path), $source->source_type);
            $chunks = $chunking->chunk($text);

            if ($chunks === []) {
                $source->update([
                    'status' => 'failed',
                    'metadata' => array_merge($source->metadata ?? [], [
                        'indexing_error' => 'No extractable text was found.',
                    ]),
                ]);

                return;
            }

            $source->chunks()->delete();

            foreach ($chunks as $index => $chunk) {
                $embedding = null;
                try {
                    $embedding = $embeddings->embed($chunk);
                } catch (Throwable $e) {
                    Log::warning('Failed to generate embedding for chunk: ' . $e->getMessage());
                }

                $source->chunks()->create([
                    'organization_id' => $source->organization_id,
                    'chunk_text' => $chunk,
                    'chunk_index' => $index,
                    'embedding' => $embedding,
                    'metadata' => [
                        'character_count' => strlen($chunk),
                    ],
                ]);
            }

            $source->update([
                'status' => 'indexed',
                'metadata' => array_merge($source->metadata ?? [], [
                    'chunk_count' => count($chunks),
                    'character_count' => strlen($text),
                    'indexed_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Knowledge source indexing failed.', [
                'source_id' => $source->id,
                'organization_id' => $source->organization_id,
                'message' => $exception->getMessage(),
            ]);

            $source->update([
                'status' => 'failed',
                'metadata' => array_merge($source->metadata ?? [], [
                    'indexing_error' => $exception->getMessage(),
                ]),
            ]);
        }
    }
}
