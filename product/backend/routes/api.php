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
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/auth/validate-employee', [AuthController::class, 'validateEmployee'])->middleware('auth:sanctum');

Route::get('/widget/settings', [\App\Http\Controllers\Api\WidgetChatController::class, 'settings']);
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