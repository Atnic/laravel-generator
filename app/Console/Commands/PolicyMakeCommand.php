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

    /**
     * Replace the model for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceModel($stub, $model)
    {
        $model = str_replace('/', '\\', $model);

        if (starts_with($model, $this->laravel->getNamespace())) {
            $namespaceModel = $model;
        } else {
            $namespaceModel = $this->laravel->getNamespace() . $model;
        }

        if (starts_with($model, '\\')) {
            $stub = str_replace('NamespacedDummyModel', trim($model, '\\'), $stub);
        } else {
            $stub = str_replace('NamespacedDummyModel', $namespaceModel, $stub);
        }

        $stub = str_replace(
            "use {$namespaceModel};\nuse {$namespaceModel};", "use {$namespaceModel};", $stub
        );

        $model = class_basename(trim($model, '\\'));

        $dummyUser = class_basename($this->userProviderModel());

        $dummyModel = camel_case($model) === 'user' ? 'model' : $model;

        $stub = str_replace('DocDummyModel', snake_case($dummyModel, ' '), $stub);

        $stub = str_replace('DummyModel', $model, $stub);

        $stub = str_replace('dummyModel', camel_case($dummyModel), $stub);

        $stub = str_replace('DummyUser', $dummyUser, $stub);

        return str_replace('DocDummyPluralModel', snake_case(str_plural($dummyModel), ' '), $stub);
    }
}
