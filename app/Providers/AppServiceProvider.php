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
    }
}
