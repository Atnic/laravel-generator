<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Foundation\Console\PolicyMakeCommand as Command;

/**
 * Policy Make Command
 */
class PolicyMakeCommand extends Command
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('model')
                    ? __DIR__.'/stubs/policy.stub'
                    : parent::getStub();
    }
}
