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
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/auth/validate-employee', [AuthController::class, 'validateEmployee'])->middleware('auth:sanctum');

Route::get('/widget/settings', [\App\Http\Controllers\Api\WidgetChatController::class, 'settings']);
Route::post('/widget/chat', [\App\Http\Controllers\Api\WidgetChatController::class, 'chat']);
Route::post('/widget/escalate', [\App\Http\Controllers\Api\WidgetChatController::class, 'escalate']);
Route::post('/widget/inbound-email', [\App\Http\Controllers\Api\WidgetChatController::class, 'inboundEmail']);

Route::post('/teams/messages', [\App\Http\Controllers\Api\TeamsBotController::class, 'handleMessage']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::post('/answers/{answer}/feedback', [FeedbackController::class, 'store']);

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
