<?php

namespace App\Models\Support;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportAiDraftSource extends Model
{
    use HasUuids;

    protected $fillable = [
        'draft_id',
        'source_id',
        'chunk_id',
        'title',
        'citation_excerpt',
        'relevance_score',
    ];

    protected $casts = [
        'relevance_score' => 'float',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(SupportAiDraft::class, 'draft_id');
    }

    public function knowledgeSource(): BelongsTo
    {
        return $this->belongsTo(KnowledgeSource::class, 'source_id');
    }

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(KnowledgeChunk::class, 'chunk_id');
    }
}
