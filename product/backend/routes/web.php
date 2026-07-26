<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminConsoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function (): void {
        Route::get('/', [AdminConsoleController::class, 'dashboard'])->name('dashboard');
        Route::get('/sources', [AdminConsoleController::class, 'sources'])->name('sources');
        Route::post('/sources', [AdminConsoleController::class, 'storeSource'])->name('sources.store');
        Route::get('/settings', function () { return redirect()->route('admin.settings.agent'); })->name('settings');
        Route::get('/settings/agent', [AdminConsoleController::class, 'settingsAgent'])->name('settings.agent');
        Route::get('/settings/design', [AdminConsoleController::class, 'settingsDesign'])->name('settings.design');
        Route::get('/settings/connect', [AdminConsoleController::class, 'settingsConnect'])->name('settings.connect');
        Route::get('/settings/deploy', [AdminConsoleController::class, 'settingsDeploy'])->name('settings.deploy');
        Route::get('/settings/pricing', [AdminConsoleController::class, 'settingsPricing'])->name('settings.pricing');
        Route::get('/super', [AdminConsoleController::class, 'superAdminPanel'])->name('super');

        Route::get('/logs', [AdminConsoleController::class, 'logs'])->name('logs');
        Route::patch('/settings', [AdminConsoleController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/preview', [AdminConsoleController::class, 'previewQuestion'])->name('settings.preview');
        Route::post('/settings/billing', [AdminConsoleController::class, 'updateBillingSettings'])->name('settings.billing.update');
        Route::post('/settings/billing/close-cycle', [AdminConsoleController::class, 'closeBillingCycle'])->name('settings.billing.close-cycle');
        Route::get('/settings/invoices/{invoice}', [AdminConsoleController::class, 'showInvoice'])->name('settings.invoices.show');
        Route::get('/settings/connect/{platform}', [AdminConsoleController::class, 'connectPlatform'])->name('settings.connect');
        Route::post('/settings/connect/{platform}', [AdminConsoleController::class, 'updateConnection'])->name('settings.connect.update');
        Route::post('/settings/upgrade', [AdminConsoleController::class, 'upgradePlan'])->name('settings.upgrade');
        Route::get('/escalations', [AdminConsoleController::class, 'escalations'])->name('escalations');
        Route::patch('/escalations/{escalation}', [AdminConsoleController::class, 'updateEscalation'])->name('escalations.update');
        
        // Employee Management
        Route::get('/leaves', [AdminConsoleController::class, 'leaves'])->name('leaves');
        Route::patch('/leaves/{leave}', [AdminConsoleController::class, 'updateLeave'])->name('leaves.update');
        Route::get('/wfh', [AdminConsoleController::class, 'wfh'])->name('wfh');
        Route::patch('/wfh/{wfh}', [AdminConsoleController::class, 'updateWfh'])->name('wfh.update');
        Route::get('/employees', [AdminConsoleController::class, 'employees'])->name('employees');
        Route::post('/employees/validate', [AdminConsoleController::class, 'validateEmployee'])->name('employees.validate');
        Route::patch('/employees/{user}', [AdminConsoleController::class, 'updateEmployee'])->name('employees.update');
        Route::get('/idcards', [AdminConsoleController::class, 'idcards'])->name('idcards');
        Route::post('/idcards', [AdminConsoleController::class, 'storeIdCard'])->name('idcards.store');
        Route::patch('/idcards/{idcard}', [AdminConsoleController::class, 'updateIdCard'])->name('idcards.update');
    });
});
