<?php

namespace Mitoop\ApiSignature;

use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/api-signature.php' => config_path('api-signature.php'),
            ]);
        }

        $this->app->singleton(ClientManager::class, function ($app) {
            return new ClientManager($app, new \GuzzleHttp\Client);
        });

        $this->app->singleton(Signature::class, function ($app) {
            return new Signature($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ClientManager::class, Signature::class];
    }
}
