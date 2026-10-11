<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EscalationController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\KnowledgeSourceController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/agent-login', [AuthController::class, 'agentLogin']);
Route::match(['get', 'post'], '/auth/agent-organization', [AuthController::class, 'agentOrganization']);
Route::get('/widget/organization-by-key', function (\Illuminate\Http\Request $request) {
    $widgetKey = $request->query('widget_key') ?? $request->input('widget_key');
    if (! $widgetKey) {
        return response()->json(['message' => 'widget_key is required.'], 400);
    }
    $setting = \App\Models\OrganizationSetting::with('organization')->where('public_widget_key', $widgetKey)->first();
    if (! $setting || ! $setting->organization) {
        return response()->json(['message' => 'Invalid widget key.'], 404);
    }
    return response()->json([
        'organization_id' => $setting->organization_id,
        'organization_name' => $setting->organization->name,
        'widget_key' => $setting->public_widget_key,
    ]);
});
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/auth/validate-employee', [AuthController::class, 'validateEmployee'])->middleware('auth:sanctum');

Route::get('/widget/settings', [\App\Http\Controllers\Api\WidgetChatController::class, 'settings']);
Route::get('/widget/config', [\App\Http\Controllers\Api\WidgetChatController::class, 'widgetConfig']);
Route::post('/widget/conversations', [\App\Http\Controllers\Api\WidgetChatController::class, 'createConversation']);
Route::post('/widget/conversations/{conversation}/messages', [\App\Http\Controllers\Api\WidgetChatController::class, 'createConversationMessage']);
Route::post('/widget/chat', [\App\Http\Controllers\Api\WidgetChatController::class, 'chat']);
Route::post('/widget/escalate', [\App\Http\Controllers\Api\WidgetChatController::class, 'escalate']);
Route::post('/widget/inbound-email', [\App\Http\Controllers\Api\WidgetChatController::class, 'inboundEmail']);

Route::post('/teams/messages', [\App\Http\Controllers\Api\TeamsBotController::class, 'handleMessage']);

Route::get('/question/escalation-check', [\App\Http\Controllers\Api\QuestionController::class, 'escalationCheck']);
Route::get('/employee/validate', [\App\Http\Controllers\Api\QuestionController::class, 'validateEmployeeById']);

// E-commerce Plugin License Validation
Route::post('/ecommerce/license/validate', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'license_key' => ['required', 'string'],
        'domain' => ['nullable', 'string'],
        'platform' => ['nullable', 'string'],
    ]);

    $license = \App\Models\EcommerceLicense::where('license_key', $validated['license_key'])->first();

    if (! $license) {
        return response()->json(['valid' => false, 'message' => 'Invalid license key.'], 404);
    }

    $domain = $validated['domain'] ?? '';
    if (! $license->isValidForDomain($domain)) {
        return response()->json(['valid' => false, 'message' => 'License is inactive, expired, or domain mismatch.'], 403);
    }

    return response()->json([
        'valid' => true,
        'license_key' => $license->license_key,
        'platform' => $license->platform,
        'status' => $license->status,
        'organization' => $license->organization->name ?? 'Aprilo Client',
        'expires_at' => $license->expires_at?->toIso8601String(),
    ]);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::post('/answers/{answer}/feedback', [FeedbackController::class, 'store']);
    Route::get('/agents', [\App\Http\Controllers\Api\AgentSupportController::class, 'index']);
    Route::post('/agents', [\App\Http\Controllers\Api\AgentSupportController::class, 'store']);
    Route::patch('/agents/{agentSupport}', [\App\Http\Controllers\Api\AgentSupportController::class, 'update']);
    Route::delete('/agents/{agentSupport}', [\App\Http\Controllers\Api\AgentSupportController::class, 'destroy']);

    Route::prefix('admin')->group(function (): void {
        Route::get('/sources', [KnowledgeSourceController::class, 'index']);
        Route::post('/sources', [KnowledgeSourceController::class, 'store']);
        Route::patch('/sources/{source}', [KnowledgeSourceController::class, 'update']);

        Route::get('/escalations', [EscalationController::class, 'index']);
        Route::get('/escalations/{escalation}', [EscalationController::class, 'show']);
        Route::patch('/escalations/{escalation}', [EscalationController::class, 'update']);

        Route::get('/metrics/summary', [MetricsController::class, 'summary']);
        Route::get('/metrics/topics', [MetricsController::class, 'topics']);

        Route::get('/settings', [SettingsController::class, 'show']);
        Route::patch('/settings', [SettingsController::class, 'update']);
    });

    // Aprilo Support Core API
    Route::prefix('support')->group(function (): void {
        Route::get('/config', [\App\Http\Controllers\Api\Support\SupportConfigController::class, 'show']);
        Route::post('/config', [\App\Http\Controllers\Api\Support\SupportConfigController::class, 'update']);
        Route::get('/inbox/summary', [\App\Http\Controllers\Api\Support\SupportInboxController::class, 'summary']);
        Route::get('/tickets/categories', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'categories']);
        Route::get('/tickets', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'index']);
        Route::post('/tickets', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'store']);
        Route::get('/tickets/{ticket}', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'show']);
        Route::post('/tickets/{ticket}/transition', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'transition']);
        Route::post('/tickets/{ticket}/priority', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'updatePriority']);
        Route::post('/tickets/{ticket}/assign', [\App\Http\Controllers\Api\Support\SupportTicketController::class, 'assign']);
        Route::get('/tickets/{ticket}/comments', [\App\Http\Controllers\Api\Support\SupportTicketCommentController::class, 'index']);
        Route::post('/tickets/{ticket}/comments', [\App\Http\Controllers\Api\Support\SupportTicketCommentController::class, 'store']);

        // AI Draft Workflow (Mandatory Human Approval)
        Route::post('/tickets/{ticket}/drafts/generate', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'generate']);
        Route::get('/tickets/{ticket}/drafts', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'index']);
        Route::get('/drafts/{draft}', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'show']);
        Route::patch('/drafts/{draft}/edit', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'edit']);
        Route::post('/drafts/{draft}/reject', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'reject']);
        Route::post('/drafts/{draft}/approve', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'approve']);
        Route::post('/drafts/{draft}/feedback', [\App\Http\Controllers\Api\Support\SupportAiDraftController::class, 'feedback']);

        // Dynamic Ticket Forms API
        Route::get('/forms', [\App\Http\Controllers\Api\Support\SupportFormController::class, 'index']);
        Route::get('/forms/{form}', [\App\Http\Controllers\Api\Support\SupportFormController::class, 'show']);
        Route::post('/forms/{form}/submit', [\App\Http\Controllers\Api\Support\SupportFormController::class, 'submit']);
    });
});

// Public Support Portal Form Intake & Fetch
// Public Support Portal API (Customer Intake & Ticket Tracking)
Route::prefix('public/support')->group(function (): void {
    Route::get('/portals/{slug}', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'portal'])->middleware('throttle:120,1');
    Route::get('/portals/{slug}/forms/{ref}', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'form'])->middleware('throttle:120,1');
    Route::post('/portals/{slug}/forms/{ref}/submit', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'submit'])->middleware('throttle:60,1');
    Route::post('/portals/{slug}/uploads', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'upload'])->middleware('throttle:30,1');
    Route::post('/portals/{slug}/tracking/request-code', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'requestCode'])->middleware('throttle:10,1');
    Route::post('/portals/{slug}/tracking/verify', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'verify'])->middleware('throttle:20,1');
    Route::get('/portals/{slug}/tracking/tickets/{ticketNumber}', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'trackedTicket'])->middleware('throttle:120,1');
    Route::post('/portals/{slug}/tracking/tickets/{ticketNumber}/replies', [\App\Http\Controllers\Api\Support\PublicSupportPortalController::class, 'reply'])->middleware('throttle:30,1');
});

Route::prefix('support/public')->group(function (): void {
    Route::get('/forms/{form}', [\App\Http\Controllers\Api\Support\SupportFormController::class, 'show'])->middleware('throttle:120,1');
    Route::post('/forms/{form}/submit', [\App\Http\Controllers\Api\Support\SupportFormController::class, 'submit'])->middleware('throttle:60,1');
});

// Customer & Organization Onboarding API
Route::prefix('onboarding')->group(function (): void {
    Route::get('/catalog', [\App\Http\Controllers\Api\OnboardingController::class, 'catalog'])->middleware('throttle:120,1');
    Route::post('/check-username', [\App\Http\Controllers\Api\OnboardingController::class, 'checkUsername'])->middleware('throttle:30,1');
    Route::post('/initiate', [\App\Http\Controllers\Api\OnboardingController::class, 'initiate'])->middleware('throttle:10,1');
    Route::post('/verify', [\App\Http\Controllers\Api\OnboardingController::class, 'verify'])->middleware('throttle:10,1');
});

// Razorpay Webhooks
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Api\RazorpayWebhookController::class, 'handle'])->middleware('throttle:120,1');

// Support Channel Webhooks (WhatsApp, Email, Website)
Route::prefix('webhooks/support')->group(function (): void {
    Route::get('/whatsapp', [\App\Http\Controllers\Api\Support\SupportWebhookController::class, 'verifyWhatsApp']);
    Route::post('/whatsapp', [\App\Http\Controllers\Api\Support\SupportWebhookController::class, 'handleWhatsApp']);
    Route::post('/email', [\App\Http\Controllers\Api\Support\SupportWebhookController::class, 'handleEmail']);
    Route::post('/website', [\App\Http\Controllers\Api\Support\SupportWebhookController::class, 'handleWebsite']);
});