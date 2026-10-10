<?php

namespace App\Models\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportAiFeedback extends Model
{
    use HasUuids;

    protected $table = 'support_ai_feedback';

    protected $fillable = [
        'organization_id',
        'draft_id',
        'user_id',
        'rating',
        'feedback_text',
        'was_edited',
    ];

    protected $casts = [
        'rating' => 'integer',
        'was_edited' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(SupportAiDraft::class, 'draft_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
