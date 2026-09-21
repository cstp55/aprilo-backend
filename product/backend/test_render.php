<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hrUser = \App\Models\User::where('email', 'hr@example.com')->first();
auth()->login($hrUser);

$request = request();
$request->setUserResolver(fn () => $hrUser);

$controller = $app->make(\App\Http\Controllers\Admin\AdminConsoleController::class);
$metrics = $app->make(\App\Services\Metrics\MetricsService::class);
$response = $controller->dashboard($request, $metrics);
$html = $response->render();
echo "Rendered view status: SUCCESS (HTML length: " . strlen($html) . " bytes)\n";
