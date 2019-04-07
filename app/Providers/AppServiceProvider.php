<?php

namespace Atnic\LaravelGenerator\Providers;

use Illuminate\Support\Facades\Gate;
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

        Gate::before(function ($user, $ability, $arguments) {
            $policy = Gate::getPolicyFor($arguments[0]);
            if (is_null($policy) && is_string($class = (is_object($arguments[0]) ? get_class($arguments[0]) : $arguments[0]))) {
                $classDirname = str_replace('/', '\\', dirname(str_replace('\\', '/', $class)));
                $guessedPolicy = $classDirname.'\\Policies\\'.class_basename($class).'Policy';
                if (class_exists($guessedPolicy))
                    Gate::policy($class, $guessedPolicy);
            }
        });
    }
}
