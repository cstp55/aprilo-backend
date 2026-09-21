<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminConsoleController;
use App\Http\Controllers\Admin\EcommerceAdminController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\SuperAdminController;
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
        // Base / Dynamic Dashboard
        Route::get('/', [AdminConsoleController::class, 'dashboard'])->name('dashboard');
        Route::get('/chat', function () { return redirect()->route('admin.dashboard', ['tab' => 'inbox']); })->name('chat');

        // Super Admin Platform Routes
        Route::prefix('super')->name('super.')->group(function (): void {
            Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/organizations', [SuperAdminController::class, 'organizations'])->name('organizations');
            Route::patch('/organizations/{organization}', [SuperAdminController::class, 'updateOrgStatus'])->name('organizations.update');
            Route::get('/revenue', [SuperAdminController::class, 'revenue'])->name('revenue');
            Route::get('/logs', [SuperAdminController::class, 'logs'])->name('logs');
        });
        Route::get('/super', function () { return redirect()->route('admin.super.dashboard'); })->name('super');

        // E-commerce Routes
        Route::prefix('ecommerce')->name('ecommerce.')->group(function (): void {
            Route::get('/', [EcommerceAdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/platforms', [EcommerceAdminController::class, 'platforms'])->name('platforms');
            Route::post('/platforms', [EcommerceAdminController::class, 'storePlatform'])->name('platforms.store');
            Route::get('/products', [EcommerceAdminController::class, 'products'])->name('products');
            Route::post('/products/{connection}/sync', [EcommerceAdminController::class, 'syncProducts'])->name('products.sync');
            Route::get('/orders', [EcommerceAdminController::class, 'orders'])->name('orders');
            Route::post('/orders/{connection}/sync', [EcommerceAdminController::class, 'syncOrders'])->name('orders.sync');
            Route::post('/embeddings/{connection}/refresh', [EcommerceAdminController::class, 'refreshEmbeddings'])->name('embeddings.refresh');
            Route::get('/licenses', [EcommerceAdminController::class, 'licenses'])->name('licenses');
            Route::post('/licenses', [EcommerceAdminController::class, 'storeLicense'])->name('licenses.store');
        });

        // Role & Permission Management Routes
        Route::prefix('roles')->name('roles')->group(function (): void {
            Route::get('/', [RoleManagementController::class, 'index']);
            Route::get('/create', [RoleManagementController::class, 'create'])->name('.create');
            Route::post('/', [RoleManagementController::class, 'store'])->name('.store');
            Route::get('/{role}/edit', [RoleManagementController::class, 'edit'])->name('.edit');
            Route::patch('/{role}', [RoleManagementController::class, 'update'])->name('.update');
            Route::post('/users/{user}/assign', [RoleManagementController::class, 'assignUserRole'])->name('.assign');
        });

        // Knowledge Base Sources
        Route::get('/sources', [AdminConsoleController::class, 'sources'])->name('sources');
        Route::post('/sources', [AdminConsoleController::class, 'storeSource'])->name('sources.store');

        // Settings & Configurations
        Route::get('/settings', function () { return redirect()->route('admin.settings.agent'); })->name('settings');
        Route::get('/settings/agent', [AdminConsoleController::class, 'settingsAgent'])->name('settings.agent');
        Route::get('/settings/design', [AdminConsoleController::class, 'settingsDesign'])->name('settings.design');
        Route::get('/settings/connect', [AdminConsoleController::class, 'settingsConnect'])->name('settings.connect');
        Route::get('/settings/deploy', [AdminConsoleController::class, 'settingsDeploy'])->name('settings.deploy');
        Route::get('/settings/pricing', [AdminConsoleController::class, 'settingsPricing'])->name('settings.pricing');

        // Review & Audit Logs
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
        
        // HR Operations
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
