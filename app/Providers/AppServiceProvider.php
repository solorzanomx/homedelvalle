<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Client;
use App\Models\Page;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\SiteSetting;
use App\Policies\ClientPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\WhatsAppService::class);
        $this->app->singleton(\App\Services\SegmentService::class);
        $this->app->singleton(\App\Services\LeadScoringService::class);
        $this->app->singleton(\App\Services\AutomationEngine::class);
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('siteSettings', SiteSetting::current());
            $view->with('navItems', Page::navItems());
            $view->with('headerMenu', Menu::forLocation('header'));
            $view->with('footerMenu', Menu::forLocation('footer'));
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // ─── RBAC: Gates ───────────────────────────────────

        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        if (Schema::hasTable('permissions')) {
            $slugs = Permission::pluck('slug');
            foreach ($slugs as $slug) {
                Gate::define($slug, function ($user) use ($slug) {
                    return $user->hasPermission($slug);
                });
            }
        }

        // ─── RBAC: Blade directive ─────────────────────────

        Blade::if('permission', function (string $permissionSlug) {
            return auth()->check() && auth()->user()->hasPermission($permissionSlug);
        });

        // ─── RBAC: Policies ────────────────────────────────

        Gate::policy(Client::class, ClientPolicy::class);
    }
}
