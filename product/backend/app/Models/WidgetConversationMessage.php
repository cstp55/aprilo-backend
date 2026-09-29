<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetConversationMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'message_type',
        'content',
        'sender_name',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WidgetConversation::class, 'conversation_id');
    }
}