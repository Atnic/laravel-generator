<?php

namespace Atnic\LaravelGenerator\Console\Commands;

use Illuminate\Console\Command;

class AppInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install '.
        '{--migrate-refresh : Migrate refresh instead of migrate}'.
        '{--migrate-fresh : Migrate fresh instead of migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'App Install';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!file_exists(base_path('.env'))) {
            $this->error('File .env not found');
            return false;
        }
        if (config('app.key') == '') {
            $this->info('Key Generate...');
            $this->call('key:generate');
        }
        if (!is_link(public_path('storage'))) {
            $this->info('Storage Link...');
            $this->call('storage:link');
        }
        if (!file_exists(app_path('Http/Controllers/HomeController.php')) && $this->confirm('Do you want to run make:auth?')) {
            $this->info('Make Auth...');
            $this->call('make:auth');
        }
        $this->info('Migrate and Seeding...');
        if ($this->option('migrate-fresh')) {
            $this->call('migrate:fresh', [ '--seed' => true ]);
        } elseif ($this->option('migrate-refresh')) {
            $this->call('migrate:refresh', [ '--seed' => true ]);
        } else {
            $this->call('migrate', [ '--seed' => true ]);
        }
        if (!file_exists(storage_path('oauth-private.key')) || !file_exists(storage_path('oauth-public.key'))) {
            $this->info('Passport Key Generate...');
            $this->call('passport:install');
        }

        return true;
    }
}
