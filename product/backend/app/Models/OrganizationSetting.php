<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'minutes_saved_per_resolved_question',
        'default_escalation_owner',
        'assistant_status',
        'chatbot_color_palette',
        'chatbot_icon',
        'chatbot_data_source',
        'chatbot_intelligence_level',
        'chatbot_ticket_creation',
        'chatbot_escalation_enabled',
        'connect_teams',
        'connect_skype',
        'connect_whatsapp',
        'connect_mail',
        'hr_desk_email',
        
        // Advanced Custom Name & Credentials
        'assistant_name',
        'teams_webhook_url',
        'teams_tenant_id',
        'teams_app_id',
        'teams_connected',
        'teams_use_webhook',
        'teams_app_password',
        
        'skype_bot_id',
        'skype_client_secret',
        'skype_connected',
        
        'whatsapp_phone_number_id',
        'whatsapp_business_account_id',
        'whatsapp_access_token',
        'whatsapp_connected',
        'whatsapp_use_sandbox',
        'whatsapp_sender_phone',
        'whatsapp_message_template',
        'whatsapp_brand_approval_status',
        
        'mail_smtp_host',
        'mail_smtp_port',
        'mail_smtp_username',
        'mail_smtp_password',
        'mail_smtp_encryption',
        'mail_connected',
        
        // Advanced Bot Workflow settings
        'chatbot_mode',
        'hr_api_connected',

        // IMAP settings
        'mail_imap_host',
        'mail_imap_port',
        'mail_imap_username',
        'mail_imap_password',
        'mail_imap_encryption',
        'mail_imap_connected',

        // LLM & Prompt settings
        'gemini_api_key',
        'gemini_model',
        'restriction_template',
        'custom_system_prompt',
        'eligibility_criteria',
        'organization_details',
        'strict_context_enforcement',
        'billing_mode',
        'usage_queries_count',
        'usage_amount_due',
    ];

    protected function casts(): array
    {
        return [
            'chatbot_escalation_enabled' => 'boolean',
            'connect_teams' => 'boolean',
            'connect_skype' => 'boolean',
            'connect_whatsapp' => 'boolean',
            'connect_mail' => 'boolean',
            
            // Connection Status Casts
            'teams_connected' => 'boolean',
            'skype_connected' => 'boolean',
            'whatsapp_connected' => 'boolean',
            'mail_connected' => 'boolean',
            
            'teams_use_webhook' => 'boolean',
            'whatsapp_use_sandbox' => 'boolean',
            
            'hr_api_connected' => 'boolean',
            'mail_imap_connected' => 'boolean',
            'strict_context_enforcement' => 'boolean',
            
            'usage_queries_count' => 'integer',
            'usage_amount_due' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
