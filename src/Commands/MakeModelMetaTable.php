<?php

namespace Dottwatson\ModelMeta\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeModelMetaTable extends Command
{
    /**
     * The name of your command. 
     * This is how your Artisan's command shall be invoked.
     */
    protected $name = 'make:model-meta-table';


    /**
     * A short description of the command's purpose.
     * You can see this working by executing
     * php artisan list
     */
    protected $description = 'Create a fresh model table meta';

    protected $tableMetaName ='';
    


    public function handle()
    {
        $tableMeta = Str::snake($this->getTableMetaInput());

        if (!Schema::hasTable($tableMeta)) {
            $this->tableMetaName = $tableMeta;
            Schema::create($tableMeta, function($table) use($tableMeta){
                   $table->engine = 'InnoDB';
                   $table->bigIncrements('meta_id')->unsigned();
                   $table->bigInteger('model_reference')->unsigned();
                   $table->string('name', 255);
                   $table->longText('value')->nullable();
                   $table->unique(['model_reference','name']);
                   $table->timestamp('created_at')->useCurrent();
                   $table->timestamp('updated_at')->useCurrent();
            });
        }
    }

    protected function getTableMetaInput()
    {
        if($this->argument('table_meta')){
            return trim($this->argument('table_meta'));
        }
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['table_meta', InputArgument::REQUIRED, 'The meta table. it will be created if not exists'],
        ];
    }



}
