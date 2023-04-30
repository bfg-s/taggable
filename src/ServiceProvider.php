<?php

namespace Lar\Tagable;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as ServiceProviderIlluminate;

class ServiceProvider extends ServiceProviderIlluminate
{
    /**
     * @var array
     */
    protected $commands = [

    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'lar' => [

        ],
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Tag::$storage = new Collection();

        register_shutdown_function(function () {
        });

        \Schema::defaultStringLength(191);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouteMiddleware();
        $this->commands($this->commands);
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }
}
