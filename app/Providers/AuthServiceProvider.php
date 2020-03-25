<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // 200227 role関連の処理追加
        // admin(1)
        Gate::define('admin', function ($user) {
            return ($user->role == 1);
        });
        // 一般ユーザー(~10)
        Gate::define('user', function ($user) {
            return ($user->role > 0 && $user->role <= 10);
        });
    }
}
