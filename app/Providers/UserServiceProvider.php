<?php
namespace Entrepreneur\Providers;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \Entrepreneur\Contracts\Repositories\UserRepository::class,
            \Entrepreneur\Repositories\UserRepository::class
        );

        $this->app->when(\Entrepreneur\ApplicationServices\UserServices::class)
             ->needs('hash')
             ->give(\Entrepreneur\Hashing\PasswordHasher::class);
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
           \Entrepreneur\Contracts\Repositories\UserRepository::class,
           \Entrepreneur\ApplicationServices\UserServices::class,
        ];
    }
}
