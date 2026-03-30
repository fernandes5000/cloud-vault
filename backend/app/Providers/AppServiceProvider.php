<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Models\DriveItem;
use App\Models\ShareLink;
use App\Policies\DriveItemPolicy;
use App\Policies\ShareLinkPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
        Gate::policy(DriveItem::class, DriveItemPolicy::class);
        Gate::policy(ShareLink::class, ShareLinkPolicy::class);

        RateLimiter::for('api', fn (Request $request) => [
            Limit::perMinute(180)->by($request->user()?->id ?: $request->ip()),
        ]);

        RateLimiter::for('auth', fn (Request $request) => [
            Limit::perMinute(10)->by(sprintf('%s|%s', $request->ip(), (string) $request->input('email'))),
        ]);

        RateLimiter::for('uploads', fn (Request $request) => [
            Limit::perMinute(240)->by($request->user()?->id ?: $request->ip()),
        ]);

        RateLimiter::for('public-shares', fn (Request $request) => [
            Limit::perMinute(60)->by($request->route('token') ?: $request->ip()),
        ]);

        RateLimiter::for('admin', fn (Request $request) => [
            Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()),
        ]);
    }
}
