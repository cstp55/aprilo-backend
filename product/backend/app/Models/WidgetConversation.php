<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WidgetConversation extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'customer_id',
        'session_id',
        'domain',
        'page_url',
        'page_title',
        'mode',
        'status',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WidgetConversationMessage::class, 'conversation_id');
    }
}