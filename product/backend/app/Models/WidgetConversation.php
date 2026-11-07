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
        'customer_name',
        'customer_email',
        'customer_phone',
        'lead_captured',
        'lead_captured_at',
        'domain',
        'page_url',
        'page_title',
        'mode',
        'status',
        'is_whatsapp_redirected',
        'whatsapp_redirected_at',
        'assigned_agent_id',
        'assigned_agent_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'lead_captured' => 'boolean',
        'lead_captured_at' => 'datetime',
        'is_whatsapp_redirected' => 'boolean',
        'whatsapp_redirected_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(AgentSupport::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WidgetConversationMessage::class, 'conversation_id');
    }
}