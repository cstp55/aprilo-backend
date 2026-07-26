<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerSource extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'answer_id',
        'source_id',
        'chunk_id',
        'citation_label',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(KnowledgeSource::class, 'source_id');
    }

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(KnowledgeChunk::class, 'chunk_id');
    }
}
