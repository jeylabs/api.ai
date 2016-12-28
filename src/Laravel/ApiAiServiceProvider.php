<?php

namespace Jeylabs\ApiAi\Laravel;

use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Jeylabs\ApiAi\ApiAi;
use Laravel\Lumen\Application as LumenApplication;

class ApiAiServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->setupConfig($this->app);
    }

    protected function setupConfig(Application $app)
    {
        $source = __DIR__ . '/config/api-ai.php';
        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('api-ai.php')]);
        } elseif ($app instanceof LumenApplication) {
            $app->configure('apiai');
        }
        $this->mergeConfigFrom($source, 'apiai');
    }

    public function register()
    {
        $this->registerBindings($this->app);
    }

    protected function registerBindings(Application $app)
    {
        $app->singleton('apiai', function ($app) {
            $config = $app['config'];
            return new ApiAi(
                $config->get('api-ai.access_token', null)
            );
        });

        $app->alias('apiai', ApiAi::class);
    }

    public function provides()
    {
        return ['apiai'];
    }
}
