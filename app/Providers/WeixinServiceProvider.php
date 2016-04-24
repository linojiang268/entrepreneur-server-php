<?php
namespace Entrepreneur\Providers;

use Illuminate\Support\ServiceProvider;

class WeixinServiceProvider extends ServiceProvider
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
        $this->app->singleton(\Entrepreneur\Services\WeixinService::class, function($app) {
            return new \Entrepreneur\Services\WeixinService(
                $app[\GuzzleHttp\ClientInterface::class],
                $app['config']['weixin']
            );
        });

        $this->app->singleton(\Entrepreneur\Services\WxMsgService::class, function ($app) {
            return new \Entrepreneur\Services\WxMsgService(
                $app[\GuzzleHttp\ClientInterface::class],
                $app['config']['weixin']
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            \Entrepreneur\Services\WeixinService::class,
            \Entrepreneur\Services\WxMsgService::class
        ];
    }
}
