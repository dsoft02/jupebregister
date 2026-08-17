<?php

namespace App\Providers;

use App\Models\Result;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Policies\ResultPolicy;
use App\Policies\SettingPolicy;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        Gate::before(fn (User $user, string $ability) => $user->isSuperAdmin() ? true : null);

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Result::class, ResultPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Paginator::useTailwind();

        RateLimiter::for('verification', fn () => Limit::perMinute(20));
    }
}
