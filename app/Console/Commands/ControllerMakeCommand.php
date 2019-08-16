<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand as Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Controller Make Command
 */
class ControllerMakeCommand extends Command
{
    protected $base_police = 'Policies';
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = null;

        if ($this->option('parent')) {
            $stub = '/stubs/controller.nested.stub';
        } elseif ($this->option('model')) {
            $stub = '/stubs/controller.model.stub';
        } elseif ($this->option('resource')) {
            $stub = '/stubs/controller.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = '/stubs/controller.api.stub';
        } elseif ($this->option('api') && ! is_null($stub)) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        $stub = $stub ?? '/stubs/controller.plain.stub';

        return __DIR__.$stub;
    }

    /**
     * Get the view stub file for the generator.
     *
     * @param string|null $method
     * @param string|null $name
     * @return string
     */
    protected function getViewStub($method = null, $name = null)
    {
        if (in_array($name, [ $this->qualifyClass('ModelController'), $this->qualifyClass('Model/ChildController') ])) {
            if ($this->option('parent')) {
                return __DIR__.'/stubs/view.nested.model.'.$method.'.stub';
            } elseif ($this->option('model') || $this->option('resource')) {
                return __DIR__.'/stubs/view.model.'.$method.'.stub';
            }
        }

        if ($this->option('parent')) {
            return __DIR__.'/stubs/view.nested.'.$method.'.stub';
        } elseif ($this->option('model') || $this->option('resource')) {
            return __DIR__.'/stubs/view.'.$method.'.stub';
        }

        return __DIR__.'/stubs/view.stub';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getTranslationStub()
    {
        return __DIR__.'/stubs/translation.stub';
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
        if (!$this->files->exists($path)) {
            $this->files->put($path, $this->buildTranslation($name));
            $this->info('Controller translation also generated successfully.');
            $this->warn($path);
        }
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
        $name = $this->getRouteName($name);
        $name = array_last(explode('.', $name));

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
        $name = $this->getRouteName($name);
        $name = array_last(explode('.', $name));
        $name = str_replace('_', ' ', $name);

        $replace = [
            'dummy_model_plural_variable' => $name,
            'dummy_model_variable' => str_singular($name),
        ];

        return str_replace(array_keys($replace), array_values($replace), $this->files->get($this->getTranslationStub()));
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
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->option('parent')) {
            $replace['DummyFullParentClass'] = $controllerNamespace.'Controller';
            $replace['DummyParentClass'] = class_basename($controllerNamespace.'Controller');
            $replace['parent_dummy_view'] = $this->getParentViewName($name);
            $replace['parent_dummy_route'] = $this->getParentRouteName($name);
        }
        $replace['dummy_view'] = $this->getViewName($name);
        $replace['dummy_route'] = $this->getRouteName($name);

        return str_replace(array_keys($replace), array_values($replace), parent::buildClass($name));
    }

    /**
     * Build the view with the given name.
     *
     * @param  string $name
     * @param  string|null $method
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildView($name, $method = null)
    {
        $replace = [];

        if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        if ($this->option('parent')) {
            $replace['parent_dummy_view'] = $this->getParentViewName($name);
            $replace['parent_dummy_route'] = $this->getParentRouteName($name);
        }
        $replace['dummy_view'] = $this->getViewName($name);
        $replace['dummy_route'] = $this->getRouteName($name);

        return str_replace(array_keys($replace), array_values($replace), $this->files->get($this->getViewStub($method, $name)));
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

        $policyClass = str_replace_first($this->rootNamespace(), $this->rootNamespace().'Policies\\', class_basename($parentModelClass)).'Policy';
        if (!$this->files->exists($this->getPath($this->base_police."\\".$policyClass))) {
            if ($this->confirm("A {$policyClass} policy does not exist. Do you want to generate it?", true)) {
                $this->call('make:policy', ['name' => $policyClass, '--model' => $parentModelClass]);
            }
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            'parent_dummy_model_variable' => snake_case(class_basename($parentModelClass)),
            'parent_dummy_model_plural_variable' => str_plural(snake_case(class_basename($parentModelClass))),
            'ParentDummyTitle' => ucwords(snake_case(class_basename($parentModelClass), ' ')),
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

        $policyClass = $this->rootNamespace().'Policies\\'.class_basename($modelClass).'Policy';
        if (!$this->files->exists($this->getPath($policyClass))) {
            if ($this->confirm("A {$policyClass} policy does not exist. Do you want to generate it?", true)) {
                $this->call('make:policy', ['name' => $policyClass, '--model' => $modelClass]);
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            'dummyModelVariable' => camel_case(class_basename($modelClass)),
            'dummy_model_variable' => snake_case(class_basename($modelClass)),
            'dummy_model_plural_variable' => str_plural(snake_case(class_basename($modelClass))),
            'DummyTitle' => ucwords(snake_case(class_basename($modelClass), ' ')),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false) return false;

        $this->createTest();
        if (!$this->option('api')) $this->generateView();
        $this->generateTranslation();
        $this->appendRouteFile();

        return null;
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

        $this->call($this->option('api') ? 'make:test-api' : 'make:test', [
            'name' => ($this->option('api') ? str_replace("Api\\", "", $controllerClass) : $controllerClass).'Test',
            '--parent' => $this->option('parent') ? : null,
            '--model' => $this->option('model') ? : null,
            '--resource' => $this->option('resource') ? : false,
        ]);
    }

    /**
     * Generate View Files
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function generateView()
    {
        if ($this->option('parent') || $this->option('model') || $this->option('resource')) {
            if ($this->option('parent')) {
                $name = $this->qualifyClass('Model/ChildController');
            } else {
                $name = $this->qualifyClass('ModelController');
            }
            $path = $this->getViewPath($name);

            if (!$this->files->exists(str_replace_last('.blade.php', '/index.blade.php', $path))) {
                foreach ([ 'index', 'create', 'show', 'edit' ] as $key => $method) {
                    $this->makeDirectory(str_replace_last('.blade.php', '/' . $method . '.blade.php', $path));
                    $this->files->put(str_replace_last('.blade.php', '/' . $method . '.blade.php', $path), $this->buildView($name, $method));
                }
            }
        }

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

        $file = $this->option('api') ? base_path('routes/api.php') : base_path('routes/web.php');
        $routeName = $this->getRouteName($name);
        $routePath = $this->getRoutePath($name);

        $routeDefinition = 'Route::get(\''.$routePath.'\', \''.$nameWithoutNamespace.'\')->name(\''.$routeName.'\');'.PHP_EOL;

        if ($this->option('parent') || $this->option('model') || $this->option('resource')) {
            $asExploded = explode('/', $routePath);
            if (count($asExploded) > 1) {
                array_pop($asExploded);
                $as = implode('.', $asExploded);
                if ($this->option('api'))
                    $routeDefinition = 'Route::apiResource(\''.$routePath.'\', \''.$nameWithoutNamespace.'\', [ \'as\' => \'api.'.$as.'\' ]);'.PHP_EOL;
                else $routeDefinition = 'Route::resource(\''.$routePath.'\', \''.$nameWithoutNamespace.'\', [ \'as\' => \''.$as.'\' ]);'.PHP_EOL;
            } else {
                if ($this->option('api'))
                    $routeDefinition = 'Route::apiResource(\''.$routePath.'\', \''.$nameWithoutNamespace.'\', [ \'as\' => \'api\' ]);'.PHP_EOL;
                else $routeDefinition = 'Route::resource(\''.$routePath.'\', \''.$nameWithoutNamespace.'\');'.PHP_EOL;
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
     * Get the view name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getParentViewName($name)
    {
        $name = Str::replaceFirst($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')).'\\', '', $name);
        $name = Str::replaceLast('Controller', '', $name);
        $names = explode('\\', $name);
        foreach ($names as $key => $value) {
            $names[$key] = snake_case($value);
        }
        if (count($names) >= 2) {
            array_pop($names);
            $parent = str_plural(array_pop($names));
            array_push($names, $parent);
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
     * Get the route name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getParentRouteName($name)
    {
        return $this->getParentViewName($name);
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class.'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class.'],
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
        ];
    }
}
