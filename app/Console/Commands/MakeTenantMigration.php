<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeTenantMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:tenant-migration {name : The name of the migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant migration file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // Generate the migration name
        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . '_' . Str::snake($name) . '.php';
        $path = database_path('migrations/tenant/' . $filename);
        
        // Make sure the tenant migrations directory exists
        if (!File::exists(database_path('migrations/tenant'))) {
            File::makeDirectory(database_path('migrations/tenant'), 0755, true);
        }
        
        // Create the migration file
        $stub = File::get(database_path('stubs/migration.create.stub'));
        
        // Replace the placeholders in the stub
        $stub = str_replace('{{ class }}', Str::studly($name), $stub);
        $stub = str_replace('{{ table }}', Str::snake(Str::pluralStudly($name)), $stub);
        
        File::put($path, $stub);
        
        $this->info('Tenant migration created successfully: ' . $filename);
    }
} 