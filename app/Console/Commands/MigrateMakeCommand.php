<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Support\Composer;
use Atnic\LaravelGenerator\Console\Commands\MigrationCreator;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseCommand;

/**
 * Migration Make Command
 */
class MigrateMakeCommand extends BaseCommand
{
    /**
     * Create a new migration install command instance.
     *
     * @param  \Atnic\LaravelGenerator\Console\Commands\MigrationCreator  $creator
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct($creator, $composer);
    }
}
