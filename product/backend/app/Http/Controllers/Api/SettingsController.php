<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $request->user()->organization_id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'assistant_status' => 'active',
            ]
        );

        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'live_chat_enabled' => ['sometimes', 'boolean'],
            'max_support_agents' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000'],
            'minutes_saved_per_resolved_question' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'assistant_status' => ['sometimes', Rule::in(['active', 'paused'])],
            'default_escalation_owner' => ['sometimes', 'nullable', 'uuid'],
            'chatbot_color_palette' => ['sometimes', 'string', 'max:50'],
            'chatbot_icon' => ['sometimes', 'string', 'max:50'],
            'chatbot_data_source' => ['sometimes', Rule::in(['documents', 'web', 'hybrid'])],
            'chatbot_intelligence_level' => ['sometimes', Rule::in(['conservative', 'standard', 'creative'])],
            'chatbot_ticket_creation' => ['sometimes', Rule::in(['automatic', 'feedback-based', 'manual'])],
            'chatbot_escalation_enabled' => ['sometimes', 'boolean'],
            'connect_teams' => ['sometimes', 'boolean'],
            'connect_skype' => ['sometimes', 'boolean'],
            'connect_whatsapp' => ['sometimes', 'boolean'],
            'connect_mail' => ['sometimes', 'boolean'],
            'hr_desk_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'chatbot_mode' => ['sometimes', Rule::in(['document_only', 'interactive_hr'])],
            'hr_api_connected' => ['sometimes', 'boolean'],
            'mail_imap_host' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mail_imap_port' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'mail_imap_username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mail_imap_password' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mail_imap_encryption' => ['sometimes', 'nullable', Rule::in(['ssl', 'tls'])],
            'mail_imap_connected' => ['sometimes', 'boolean'],
            'gemini_api_key' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gemini_model' => ['sometimes', 'string', 'max:100'],
            'restriction_template' => ['sometimes', 'string', Rule::in(['strict_retrieval', 'hr_policy', 'general_faq', 'custom'])],
            'custom_system_prompt' => ['sometimes', 'nullable', 'string'],
            'eligibility_criteria' => ['sometimes', 'nullable', 'string'],
            'organization_details' => ['sometimes', 'nullable', 'string'],
            'strict_context_enforcement' => ['sometimes', 'boolean'],
        ]);

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $request->user()->organization_id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'assistant_status' => 'active',
            ]
        );

        $settings->update($validated);

        return response()->json(['settings' => $settings->fresh()]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role, [UserRole::Owner->value, UserRole::HrAdmin->value], true),
            403,
            'Admin access is required.'
        );
    }
}
