<?php

namespace Atnic\LaravelGenerator\Providers;

use Atnic\LaravelGenerator\Console\Commands\ControllerApiMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\ControllerMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\FactoryMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\FilterMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\MigrationCreator;
use Atnic\LaravelGenerator\Console\Commands\MigrateMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\ModelMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\TestApiMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\TestMakeCommand;
use Atnic\LaravelGenerator\Console\Commands\PolicyMakeCommand;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $devCommands = [
        'ControllerApiMake' => 'command.controller.api.make',
        'ControllerMake' => 'command.controller.make',
        'FactoryMake' => 'command.factory.make',
        'FilterMake' => 'command.filter.make',
        'MigrateMake' => 'command.migrate.make',
        'ModelMake' => 'command.model.make',
        'PolicyMake' => 'command.policy.make',
        'TestApiMake' => 'command.test.api.make',
        'TestMake' => 'command.test.make',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCreator();
        $this->registerCommands($this->devCommands);
    }

    /**
     * Register the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            call_user_func_array([$this, "register{$command}Command"], []);
        }

        $this->commands(array_values($commands));
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerControllerApiMakeCommand()
    {
        $this->app->singleton('command.controller.api.make', function ($app) {
            return new ControllerApiMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerControllerMakeCommand()
    {
        $this->app->singleton('command.controller.make', function ($app) {
            return new ControllerMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFactoryMakeCommand()
    {
        $this->app->singleton('command.factory.make', function ($app) {
            return new FactoryMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFilterMakeCommand()
    {
        $this->app->singleton('command.filter.make', function ($app) {
            return new FilterMakeCommand($app['files']);
        });
    }

    /**
     * Register the migration creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function ($app) {
            return new MigrationCreator($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateMakeCommand()
    {
        $this->app->singleton('command.migrate.make', function ($app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['migration.creator'];

            $composer = $app['composer'];

            return new MigrateMakeCommand($creator, $composer);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerModelMakeCommand()
    {
        $this->app->singleton('command.model.make', function ($app) {
            return new ModelMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerTestApiMakeCommand()
    {
        $this->app->singleton('command.test.api.make', function ($app) {
            return new TestApiMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerTestMakeCommand()
    {
        $this->app->singleton('command.test.make', function ($app) {
            return new TestMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPolicyMakeCommand()
    {
        $this->app->singleton('command.policy.make', function ($app) {
            return new PolicyMakeCommand($app['files']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(array_values($this->devCommands), [ 'migration.creator' ]);
    }
}
