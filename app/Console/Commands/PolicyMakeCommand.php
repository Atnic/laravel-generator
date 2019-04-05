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
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        if (!$this->alreadyExists('ModelPolicy')) {
            $this->files->put($this->getPath($this->qualifyClass('ModelPolicy')), $this->buildClass($this->qualifyClass('ModelPolicy')));
        }
        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.');
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
        $stub = $this->files->get($this->getStub($name));

        $parent = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        $stub = $this->replaceUserNamespace(
            $parent //parent::buildClass($name)
        );

        $model = $this->option('model');

        return $model ? $this->replaceModel($stub, $model) : $stub;
    }

    /**
     * Get the stub file for the generator.
     *
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
