<?php

namespace App\Models\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportAiDraft extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'ticket_id',
        'trigger_comment_id',
        'trigger_message_preview',
        'proposed_reply',
        'edited_reply',
        'status',
        'is_stale',
        'model',
        'confidence_score',
        'generation_metadata',
        'reviewed_by',
        'reviewed_at',
        'dispatched_at',
        'dispatched_comment_id',
        'dispatch_channel',
        'dispatch_status',
        'dispatch_error',
        'rejection_reason',
    ];

    protected $casts = [
        'is_stale' => 'boolean',
        'confidence_score' => 'float',
        'generation_metadata' => 'array',
        'reviewed_at' => 'datetime',
        'dispatched_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function triggerComment(): BelongsTo
    {
        return $this->belongsTo(SupportTicketComment::class, 'trigger_comment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function dispatchedComment(): BelongsTo
    {
        return $this->belongsTo(SupportTicketComment::class, 'dispatched_comment_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(SupportAiDraftSource::class, 'draft_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(SupportAiFeedback::class, 'draft_id');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'pending_review');
    }

    public function getEffectiveReplyAttribute(): string
    {
        return ! empty($this->edited_reply) ? $this->edited_reply : $this->proposed_reply;
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
