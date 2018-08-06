<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

/**
 * Filter Make Command
 */
class FilterMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:filter';

    /**
     * Laravel application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new filter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Filter';

    public function __construct(Filesystem $files, Application $app)
    {
        parent::__construct($files);

        $this->app = $app;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/filter.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->app['config']->get('filters.namespace');
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        if ($this->app['config']->get('filters.path'))
            return $this->app['config']->get('filters.path') . '/' . class_basename($name) . '.php';
        else return parent::getPath($name);
    }
}
