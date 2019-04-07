<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Foundation\Console\PolicyMakeCommand as Command;

/**
 * Policy Make Command
 */
class PolicyMakeCommand extends Command
{

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (!$this->alreadyExists('ModelPolicy') && $this->hasOption('model')) {
            $name = $this->qualifyClass('ModelPolicy');

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
     * Build the class with the given name.
     *
     * @param  string $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        if ($name == $this->qualifyClass('ModelPolicy')) {
            $stub = $this->files->get($this->getStub($name));

            $parent = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

            $stub = $this->replaceUserNamespace($parent);

            $model = $this->option('model');

            return $model ? $this->replaceModel($stub, $model) : $stub;
        }

        return parent::buildClass($name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @param null $name
     * @return string
     */
    protected function getStub($name = null)
    {
        if ($name == $this->qualifyClass('ModelPolicy')) return __DIR__.'/stubs/policy.model.stub';

        return $this->option('model')
            ? __DIR__.'/stubs/policy.stub'
            : parent::getStub();
    }
}
