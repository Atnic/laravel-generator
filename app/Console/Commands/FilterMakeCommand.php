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
     * @param string|null $name
     * @return string
     */
    protected function getStub($name = null)
    {
        if ($name == $this->qualifyClass('BaseFilter')) return __DIR__.'/stubs/filter.base.stub';
        return __DIR__.'/stubs/filter.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (!$this->alreadyExists('BaseFilter')) {
            $name = $this->qualifyClass('BaseFilter');

            $path = $this->getPath($name);

            // Next, we will generate the path to the location where this class' file should get
            // written. Then, we will build the class and make the proper replacements on the
            // stub files so that it gets the correctly formatted namespace and class name.
            $this->makeDirectory($path);

            $this->files->put($path, $this->buildClass($name));
        }

        return parent::handle();
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

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        if ($name == $this->qualifyClass('BaseFilter')) {
            $stub = $this->files->get($this->getStub($name));

            return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
        }

        return parent::buildClass($name);
    }
}
