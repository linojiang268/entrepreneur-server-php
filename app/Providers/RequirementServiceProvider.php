<?php
namespace Entrepreneur\Providers;

use Illuminate\Support\ServiceProvider;

class RequirementServiceProvider extends ServiceProvider
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
            \Entrepreneur\Contracts\Repositories\RequirementRepository::class,
            \Entrepreneur\Repositories\RequirementRepository::class
        );
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
           \Entrepreneur\Contracts\Repositories\RequirementRepository::class,
        ];
    }
}
