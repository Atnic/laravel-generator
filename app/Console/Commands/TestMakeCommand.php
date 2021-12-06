<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Foundation\Console\TestMakeCommand as Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class TestMakeCommand extends Command
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('unit')) {
            return parent::getStub();
        }

        if ($this->option('parent')) {
            $stub = '/stubs/test.nested.stub';
        } elseif ($this->option('model')) {
            $stub = '/stubs/test.model.stub';
        } elseif ($this->option('resource')) {
            $stub = '/stubs/test.stub';
        } else {
            $stub = '/stubs/test.plain.stub';
        }

        if ($this->option('api')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        return $this->resolveStubPath($stub);
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
     * Get the destination class app path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getAppPath($name)
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
            if ($this->option('parent')) {
                $replace = array_merge($replace, [
                    'dummy_action_index' => 'route(\''.$this->getRouteName($name).'.index\', [ $'.$replace['parent_dummy_model_variable'].'->getKey() ])',
                    'dummy_action_create' => 'route(\''.$this->getRouteName($name).'.create\', [ $'.$replace['parent_dummy_model_variable'].'->getKey() ])',
                    'dummy_action_store' => 'route(\''.$this->getRouteName($name).'.store\', [ $'.$replace['parent_dummy_model_variable'].'->getKey() ])',
                    'dummy_action_show' => 'route(\''.$this->getRouteName($name).'.show\', [ $'.$replace['parent_dummy_model_variable'].'->getKey(), $'.$replace['dummy_model_variable'].'->getKey() ])',
                    'dummy_action_edit' => 'route(\''.$this->getRouteName($name).'.edit\', [ $'.$replace['parent_dummy_model_variable'].'->getKey(), $'.$replace['dummy_model_variable'].'->getKey()  ])',
                    'dummy_action_update' => 'route(\''.$this->getRouteName($name).'.update\', [ $'.$replace['parent_dummy_model_variable'].'->getKey(), $'.$replace['dummy_model_variable'].'->getKey()  ])',
                    'dummy_action_destroy' => 'route(\''.$this->getRouteName($name).'.destroy\', [ $'.$replace['parent_dummy_model_variable'].'->getKey(), $'.$replace['dummy_model_variable'].'->getKey()  ])',
                ]);
            } else {
                $replace = array_merge($replace, [
                    'dummy_action_index' => 'route(\''.$this->getRouteName($name).'.index\')',
                    'dummy_action_create' => 'route(\''.$this->getRouteName($name).'.create\')',
                    'dummy_action_store' => 'route(\''.$this->getRouteName($name).'.store\')',
                    'dummy_action_show' => 'route(\''.$this->getRouteName($name).'.show\', [ $'.$replace['dummy_model_variable'].'->getKey() ])',
                    'dummy_action_edit' => 'route(\''.$this->getRouteName($name).'.edit\', [ $'.$replace['dummy_model_variable'].'->getKey() ])',
                    'dummy_action_update' => 'route(\''.$this->getRouteName($name).'.update\', [ $'.$replace['dummy_model_variable'].'->getKey() ])',
                    'dummy_action_destroy' => 'route(\''.$this->getRouteName($name).'.destroy\', [ $'.$replace['dummy_model_variable'].'->getKey() ])',
                ]);
            }
        }

        $replace['dummy_view'] = $this->getViewName($name);
        $replace['dummy_route'] = $this->getRouteName($name);

        return str_replace(array_keys($replace), array_values($replace), $this->replaceUserNamespace(parent::buildClass($name)));
    }

    /**
     * Replace the User model namespace.
     *
     * @param  string  $stub
     * @return string
     */
    protected function replaceUserNamespace($stub)
    {
        if (! config('auth.providers.users.model')) {
            return $stub;
        }

        return str_replace(
            $this->rootNamespace().'User',
            config('auth.providers.users.model'),
            $stub
        );
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    protected function buildParentReplacements()
    {
        $parentModelClass = $this->parseModel($this->option('parent'));
        if (!$this->files->exists($this->getAppPath($parentModelClass))) {
            if ($this->confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $parentModelClass, '-m' => true, '-f' => true]);
            }
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
            'parent_dummy_model_variable' => Str::snake(class_basename($parentModelClass)),
            'ParentDummyTitle' => ucwords(Str::snake(class_basename($parentModelClass), ' ')),
        ];
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));
        if (!$this->files->exists($this->getAppPath($modelClass))) {
            if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $modelClass, '-m' => true, '-f' => true]);
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
            'dummyModelVariable' => Str::camel(class_basename($modelClass)),
            'dummy_model_variable' => Str::snake(class_basename($modelClass)),
            'dummy_model_plural_variable' => Str::plural(Str::snake(class_basename($modelClass))),
            'DummyTitle' => ucwords(Str::snake(class_basename($modelClass), ' ')),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $rootNamespace = is_dir(app_path('Models')) ? $rootNamespace.'Models\\' : $rootNamespace;
            $model = $rootNamespace.$model;
        }

        return $model;
    }

    /**
     * Get the route name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getViewName($name)
    {
        $name = Str::replaceFirst($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')).'\\', '', $name);
        $name = Str::replaceLast('ControllerTest', '', $name);
        $names = explode('\\', $name);
        foreach ($names as $key => $value) {
            $names[$key] = Str::snake($value);
        }
        if ($this->option('parent') && count($names) >= 2) {
            $model = Str::plural(array_pop($names));
            $parent = Str::plural(array_pop($names));
            array_push($names, $parent, $model);
        } elseif (($this->option('model') || $this->option('resource')) && count($names) >= 1) {
            $model = Str::plural(array_pop($names));
            array_push($names, $model);
        }
        $name = implode('.', $names);

        return str_replace('\\', '.', $name);
    }

    /**
     * Get the route name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getRouteName($name)
    {
        return $this->getViewName($name);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['api', null, InputOption::VALUE_NONE, 'Generate an api controller test.'],
            ['parent', null, InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller test.'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller test for the given model.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller test.'],
        ]);
    }
}
