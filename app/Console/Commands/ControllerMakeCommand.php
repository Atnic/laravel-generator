<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Routing\Console\ControllerMakeCommand as Command;

/**
 * Controller Make Command
 */
class ControllerMakeCommand extends Command
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('parent')) {
            return __DIR__.'/stubs/controller.nested.stub';
        } elseif ($this->option('model')) {
            return __DIR__.'/stubs/controller.model.stub';
        } elseif ($this->option('resource')) {
            return __DIR__.'/stubs/controller.stub';
        }

        return __DIR__.'/stubs/controller.plain.stub';
    }

    /**
     * Get the view stub file for the generator.
     *
     * @param string|null $method
     * @return string
     */
    protected function getViewStub($method = null)
    {
        if ($this->option('parent')) {
            // return __DIR__.'/stubs/view.nested.'.$method.'.stub'; //Unavailable yet
            return __DIR__.'/stubs/view.model.'.$method.'.stub';
        } elseif ($this->option('model') || $this->option('resource')) {
            return __DIR__.'/stubs/view.model.'.$method.'.stub';
        }

        return __DIR__.'/stubs/view.stub';
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

        $replace['dummy_view'] = $this->getViewName($name);
        $replace['dummy_route'] = $this->getRouteName($name);

        return str_replace(array_keys($replace), array_values($replace), parent::buildClass($name));
    }

    /**
     * Build the view with the given name.
     *
     * @param  string  $name
     * @param  string|null  $method
     * @return string
     */
    protected function buildView($name, $method = null)
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

        return str_replace(array_keys($replace), array_values($replace), $this->files->get($this->getViewStub($method)));
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    protected function buildParentReplacements()
    {
        $parentModelClass = $this->parseModel($this->option('parent'));
        if (!$this->files->exists($this->getPath($parentModelClass))) {
            if ($this->confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => str_replace($this->rootNamespace(), '', $parentModelClass), '-m' => true, '-f' => true]);
            }
        }

        $policyClass = str_replace_first($this->rootNamespace(), $this->rootNamespace().'Policies\\', $parentModelClass).'Policy';
        if (!$this->files->exists($this->getPath($policyClass))) {
            if ($this->confirm("A {$policyClass} policy does not exist. Do you want to generate it?", true)) {
                $this->call('make:policy', ['name' => $policyClass, '--model' => class_basename($parentModelClass)]);
            }
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            'parent_dummy_model_variable' => snake_case(class_basename($parentModelClass)),
            'ParentDummyTitle' => title_case(snake_case(class_basename($parentModelClass), ' ')),
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
        if (!$this->files->exists($this->getPath($modelClass))) {
            if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => str_replace($this->rootNamespace(), '', $modelClass), '-m' => true, '-f' => true]);
            }
        }

        $policyClass = str_replace_first($this->rootNamespace(), $this->rootNamespace().'Policies\\', $modelClass).'Policy';
        if (!$this->files->exists($this->getPath($policyClass))) {
            if ($this->confirm("A {$policyClass} policy does not exist. Do you want to generate it?", true)) {
                $this->call('make:policy', ['name' => $policyClass, '--model' => class_basename($modelClass)]);
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            'dummyModelVariable' => camel_case(class_basename($modelClass)),
            'dummy_model_variable' => snake_case(class_basename($modelClass)),
            'dummy_model_plural_variable' => str_plural(snake_case(class_basename($modelClass))),
            'DummyTitle' => title_case(snake_case(class_basename($modelClass), ' ')),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        if (parent::handle() === false) return false;

        $this->createTest();
        $this->generateView();
        $this->appendRouteFile();
    }

    /**
     * Create a test for the controller.
     *
     * @return void
     */
    protected function createTest()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $controllerClass = Str::replaceFirst($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')).'\\', '', $name);

        $this->call('make:test', [
            'name' => $controllerClass.'Test',
            '--parent' => $this->option('parent') ? : null,
            '--model' => $this->option('model') ? : null,
            '--resource' => $this->option('resource') ? : false,
        ]);
    }

    /**
     * Generate View Files
     * @return void
     */
    protected function generateView()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getViewPath($name);

        if ($this->option('parent') || $this->option('model') || $this->option('resource')) {
            foreach ([ 'index', 'create', 'show', 'edit' ] as $key => $method) {
                $this->makeDirectory(str_replace_last('.blade.php', '/' . $method . '.blade.php', $path));
                $this->files->put(str_replace_last('.blade.php', '/' . $method . '.blade.php', $path), $this->buildView($name, $method));
            }
        } else {
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildView($name));
        }

        $this->info('View also generated successfully.');
    }

    /**
     * Append Route Files
     * @return void
     */
    protected function appendRouteFile()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $nameWithoutNamespace = str_replace($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')).'\\', '', $name);

        $file = base_path('routes/web.php');
        $routeName = $this->getRouteName($name);
        $routePath = $this->getRoutePath($name);

        $routeDefinition = 'Route::get(\''.$routePath.'\', \''.$nameWithoutNamespace.'\')->name(\''.$routeName.'\');'.PHP_EOL;

        if ($this->option('parent') || $this->option('model') || $this->option('resource')) {
            $asExploded = explode('/', $routePath);
            if (count($asExploded) > 1) {
                array_pop($asExploded);
                $as = implode('.', $asExploded);
                $routeDefinition = 'Route::resource(\''.$routePath.'\', \''.$nameWithoutNamespace.'\', [ \'as\' => \''.$as.'\' ]);'.PHP_EOL;
            } else {
                $routeDefinition = 'Route::resource(\''.$routePath.'\', \''.$nameWithoutNamespace.'\');'.PHP_EOL;
            }
        }

        file_put_contents($file, $routeDefinition, FILE_APPEND);

        $this->warn($file.' modified.');
    }

    /**
     * Get the view name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getViewName($name)
    {
        $name = Str::replaceFirst($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')).'\\', '', $name);
        $name = Str::replaceLast('Controller', '', $name);
        $names = explode('\\', $name);
        foreach ($names as $key => $value) {
            $names[$key] = snake_case($value);
        }
        if ($this->option('parent') && count($names) >= 2) {
            $model = str_plural(array_pop($names));
            $parent = str_plural(array_pop($names));
            array_push($names, $parent, $model);
        } elseif (($this->option('model') || $this->option('resource')) && count($names) >= 1) {
            $model = str_plural(array_pop($names));
            array_push($names, $model);
        }
        $name = implode('.', $names);

        return str_replace('\\', '.', $name);
    }

    /**
     * Get the view path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getViewPath($name)
    {
        $name = str_replace('.', '/', $this->getViewName($name));

        return base_path().'/resources/views/'.$name.'.blade.php';
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
     * Get the route path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getRoutePath($name)
    {
        $routeName = $this->getRouteName($name);
        $routeNameExploded = explode('.', $routeName);
        $routePath = str_replace('.', '/', $this->getViewName($routeName));
        if ($this->option('parent') && count($routeNameExploded) >= 2) {
            $routePath = str_replace_last('/', '.', $routePath);
        }
        return $routePath;
    }
}
