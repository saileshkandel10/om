<?php

namespace App\Providers;

use App\Models\QuizAttempt;
use App\Policies\QuizAttemptPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Paginator::useBootstrapFive();
        $this->registerOpenSpoutAutoloader();
        $this->configureDefaults();
        $this->configurePolicies();
        $this->configureRateLimiters();
    }

    /**
     * Register OpenSpout autoloader fallback.
     */
    protected function registerOpenSpoutAutoloader(): void
    {
        spl_autoload_register(function ($class) {
            if (str_starts_with($class, 'OpenSpout\\')) {
                $relative = str_replace('\\', '/', substr($class, 10));
                $path = base_path('vendor/openspout/openspout/src/'.$relative.'.php');
                if (file_exists($path)) {
                    require_once $path;
                }
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure authorization policies.
     */
    protected function configurePolicies(): void
    {
        Gate::policy(QuizAttempt::class, QuizAttemptPolicy::class);
    }

    /**
     * Configure API rate limiters for quiz endpoints.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('quiz-start', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('quiz-submit', function ($request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('quiz-api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
