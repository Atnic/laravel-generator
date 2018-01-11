<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

/**
 * MigrationCreator
 */
class MigrationCreator extends BaseMigrationCreator
{
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs/migration';
    }
}
