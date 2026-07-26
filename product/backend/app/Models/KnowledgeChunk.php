<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeChunk extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'source_id',
        'chunk_text',
        'chunk_index',
        'section_title',
        'metadata',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'embedding' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(KnowledgeSource::class, 'source_id');
    }
}
