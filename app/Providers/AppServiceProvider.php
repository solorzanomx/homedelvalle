<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use App\Events\DocumentoFirmadoGoogle;
use App\Listeners\ProcesarDocumentoFirmadoGoogle;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
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
        $this->app->singleton(\App\Services\AI\AIManager::class);
        $this->app->singleton(\App\Services\GoogleDriveService::class, fn() => new \App\Services\GoogleDriveService());
        $this->app->singleton(\App\Services\GoogleESignatureService::class, fn($app) => new \App\Services\GoogleESignatureService($app->make(\App\Services\GoogleDriveService::class)));
        $this->app->singleton(\App\Services\GoogleDocsService::class);
    }

    public function boot(): void
    {
        // ─── Eventos Google eSignature ────────────────────
        Event::listen(DocumentoFirmadoGoogle::class, ProcesarDocumentoFirmadoGoogle::class);

        // Locale español para Carbon (fechas)
        Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_MX.UTF-8', 'es_ES.UTF-8', 'es_MX', 'es_ES', 'es');

        // Force HTTPS in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            try {
                $settings = SiteSetting::current();
            } catch (\Throwable $e) {
                $settings = null;
            }

            // Garantizar que siempre exista un objeto con defaults
            if (!$settings) {
                $settings = new SiteSetting([
                    'site_name' => 'Home del Valle',
                    'primary_color' => '#667eea',
                    'secondary_color' => '#764ba2',
                ]);
            }

            $view->with('siteSettings', $settings);

            try {
                $view->with('navItems', Page::navItems());
                $view->with('headerMenu', Menu::forLocation('header'));
                $view->with('footerMenu', Menu::forLocation('footer'));
            } catch (\Throwable $e) {
                $view->with('navItems', collect());
                $view->with('headerMenu', collect());
                $view->with('footerMenu', collect());
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower($request->input('email', '')) . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->with('error', 'Demasiados intentos. Espera 1 minuto antes de intentar de nuevo.');
            });
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('public-form', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return back()->with('error', 'Demasiados envíos. Por favor espera unos minutos antes de intentar de nuevo.');
            });
        });

        RateLimiter::for('newsletter', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json(['ok' => false, 'message' => 'Demasiados intentos.'], 429);
            });
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
