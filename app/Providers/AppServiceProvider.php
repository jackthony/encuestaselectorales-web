<?php

namespace App\Providers;

use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Vote\Contracts\GeographicValidator;
use App\Domain\Vote\Contracts\VotePrivacy;
use App\Infrastructure\Media\OgThumbnailCache;
use App\Infrastructure\Media\OgThumbnailRenderer;
use App\Infrastructure\Persistence\Repositories\EloquentSurveyRoundQuery;
use App\Infrastructure\Persistence\Repositories\EloquentTerritoryCatalog;
use App\Infrastructure\Security\AesGcmVotePrivacy;
use App\Infrastructure\Security\ConfiguredGeographicValidator;
use App\Infrastructure\Security\TrustedClientIp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TerritoryCatalog::class, EloquentTerritoryCatalog::class);
        $this->app->bind(SurveyRoundQuery::class, EloquentSurveyRoundQuery::class);
        $this->app->bind(VotePrivacy::class, AesGcmVotePrivacy::class);
        $this->app->bind(GeographicValidator::class, ConfiguredGeographicValidator::class);

        $this->app->singleton(OgThumbnailRenderer::class, static fn (): OgThumbnailRenderer => new OgThumbnailRenderer(
            backgroundPath: public_path('assets/miniatura-compartir/og-results-background-1200x630.png'),
            logoPath: public_path('assets/miniatura-compartir/brand-logo-horizontal-cleaned.png'),
            domainLockupPath: public_path('assets/miniatura-compartir/brand-domain-lockup-cleaned.png'),
            boldFontPath: resource_path('fonts/Inter-Bold.ttf'),
            semiBoldFontPath: resource_path('fonts/Inter-SemiBold.ttf'),
        ));
        $this->app->singleton(OgThumbnailCache::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('votes', static function (Request $request): Limit {
            try {
                $key = app(TrustedClientIp::class)->resolve($request);
            } catch (\RuntimeException) {
                $key = 'untrusted:'.(string) $request->server('REMOTE_ADDR', 'unknown');
            }

            return Limit::perMinute(10)->by($key);
        });
    }
}
