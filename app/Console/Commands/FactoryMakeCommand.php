<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Database\Console\Factories\FactoryMakeCommand as Command;

class FactoryMakeCommand extends Command
{
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
}
