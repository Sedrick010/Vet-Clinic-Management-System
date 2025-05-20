<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTableSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:check {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the schema of a specific table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');
        
        $this->info("Checking schema for table: $table");
        
        $columns = DB::select("SHOW COLUMNS FROM $table");
        
        $this->table(
            ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'],
            collect($columns)->map(function ($column) {
                return [
                    'Field' => $column->Field,
                    'Type' => $column->Type,
                    'Null' => $column->Null,
                    'Key' => $column->Key,
                    'Default' => $column->Default,
                    'Extra' => $column->Extra,
                ];
            })
        );
        
        return 0;
    }
} 