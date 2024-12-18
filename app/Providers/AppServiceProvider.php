<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

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
      ResetPassword::createUrlUsing(function (User $user, string $token) {
        return 'http://115.147.32.2:9000/reset-password?token=' . $token . '&email=' . $user->email;
      });
    }
}
