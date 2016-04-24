<?php

namespace Entrepreneur\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->extendValidator();
        $this->extendAuthManager();
    }

    private function extendAuthManager()
    {
        Auth::provider('eloquent', function ($app, $config) {
            return new \Entrepreneur\Auth\UserProvider(new \Entrepreneur\Hashing\PasswordHasher(),
                $config['model']);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    private function extendValidator()
    {
        Validator::extend('mobile', function($attribute, $value, $parameters) {
            return preg_match('/^1[34578]\d{9}$/', $value) > 0;
        });
        Validator::extend('phone', function($attribute, $value, $parameters) {
            return (preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/', $value) > 0)
            || (preg_match('/^1[34578]\d{9}$/', $value) > 0);
        });
    }
}
