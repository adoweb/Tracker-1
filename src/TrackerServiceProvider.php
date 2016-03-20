<?php

namespace Kenarkose\Tracker;


use Illuminate\Support\ServiceProvider;

class TrackerServiceProvider extends ServiceProvider {

    const version = '1.0.3';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['tracker'];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTracker();
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/resources/config.php' => config_path('tracker.php')
        ]);

        $this->publishes([
            __DIR__ . '/migrations/' => database_path('/migrations')
        ], 'migrations');
    }

    /**
     * Registers the tracker service
     */
    protected function registerTracker()
    {
        $this->app->singleton(
            'tracker',
            'Kenarkose\Tracker\Tracker'
        );
    }
}