<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Database\Console\Factories\FactoryMakeCommand as Command;
use Symfony\Component\Console\Input\InputOption;

class FactoryMakeCommand extends Command
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/factory.stub';
    }
}
