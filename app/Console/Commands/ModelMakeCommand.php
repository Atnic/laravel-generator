<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as Command;
use Illuminate\Support\Str;

/**
 * Model Make Command
 */
class ModelMakeCommand extends Command
{
    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle()
    {
        $this->createFilter();

        if (!$this->alreadyExists('BaseModel')) {
            $name = $this->qualifyClass('BaseModel');

            $path = $this->getPath($name);

            // Next, we will generate the path to the location where this class' file should get
            // written. Then, we will build the class and make the proper replacements on the
            // stub files so that it gets the correctly formatted namespace and class name.
            $this->makeDirectory($path);

            $this->files->put($path, $this->buildClass($name));
        }

        if (parent::handle() === false) return false;

        $this->generateTranslation();

        return true;
    }

    /**
     * Create a model factory for the model.
     *
     * @return void
     */
    protected function createFactory()
    {
        $factory = class_basename($this->argument('name'));

        $this->call('make:factory', [
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
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
        if ($name == $this->qualifyClass('BaseModel')) {
            $stub = $this->files->get($this->getStub($name));

            return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
        }

        return parent::buildClass($name);
    }

    /**
     * Create a filter for the model.
     *
     * @return void
     */
    protected function createFilter()
    {
        $model = Str::studly(class_basename($this->argument('name')));

        $this->call('make:filter', [
            'name' => $model.'Filter',
        ]);
    }

    /**
     * Get the stub file for the generator.
     *
     * @param string|null $name
     * @return string
     */
    protected function getStub($name = null)
    {
        if ($name == $this->qualifyClass('BaseModel')) return $this->resolveStubPath('/stubs/model.base.stub');

        return parent::getStub();
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getTranslationStub()
    {
        return $this->resolveStubPath('/stubs/translation.stub');
    }

    /**
     * Generate Translation File
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function generateTranslation()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getTranslationPath($name);

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildTranslation($name));

        $this->info('Translation also generated successfully.');
        $this->warn($path);
    }

    /**
     * Get the translation path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getTranslationPath($name)
    {
        $name = $this->getTranslationName($name);

        return base_path().'/resources/lang/en/'.$name.'.php';
    }

    /**
     * Get the translation name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getTranslationName($name)
    {
        $name = Str::replaceFirst($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')).'\\', '', $name);
        $name = Str::replaceLast('Models\\', '', $name);
        $name = Str::plural(Str::snake($name));

        return $name;
    }


    /**
     * Build the translation with the given name.
     *
     * @param  string $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildTranslation($name)
    {
        $replace = [
            'dummy_model_plural_variable' => Str::plural(Str::snake(class_basename($name), ' ')),
            'dummy_model_variable' => Str::snake(class_basename($name), ' '),
        ];

        return str_replace(array_keys($replace), array_values($replace), $this->files->get($this->getTranslationStub()));
    }
}
