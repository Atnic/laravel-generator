<?php

namespace Atnic\LaravelGenerator\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/generator.php' => config_path('generator.php'),
            __DIR__.'/../../config/filters.php' => config_path('filters.php'),
        ], 'config');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'generator');
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/generator')
        ], 'views');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/generator.php', 'generator'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../../config/filters.php', 'filters'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../../config/guards-api.php', 'auth.guards.api'
        );
    }
}
