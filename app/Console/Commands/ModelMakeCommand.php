<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Foundation\Console\ModelMakeCommand as Command;

/**
 * Model Make Command
 */
class ModelMakeCommand extends Command
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createFilter();

        return parent::handle();
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
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = $this->option('pivot') ?
            Str::snake(class_basename($this->argument('name'))) :
            Str::plural(Str::snake(class_basename($this->argument('name'))));

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('pivot')) {
            return __DIR__.'/stubs/pivot.model.stub';
        }

        return __DIR__.'/stubs/model.stub';
    }
}
