<?php

namespace App\Providers;

use App\Services\AI\AiProviderInterface;
use App\Services\AI\GeminiAiProvider;
use Illuminate\Support\ServiceProvider;

use App\Services\Navigation\NavigationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiProviderInterface::class, GeminiAiProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(NavigationService $navigation): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Custom Blade Permission Directives
        Blade::if('canPermission', function (string $permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        Blade::if('hasRole', function (string|array $role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // View Composer for Dynamic Admin Sidebar
        View::composer('admin.layout', function ($view) use ($navigation) {
            $user = auth()->user();
            $menu = $navigation->getSidebarMenu($user);
            $view->with('sidebarMenu', $menu);
        });
    }
}
