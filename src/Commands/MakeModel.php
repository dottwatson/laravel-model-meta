<?php

namespace Dottwatson\ModelMeta\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeModel extends GeneratorCommand
{
    /**
     * The name of your command. 
     * This is how your Artisan's command shall be invoked.
     */
    protected $name = 'make:model-meta'; 

    /**
     * A short description of the command's purpose.
     * You can see this working by executing
     * php artisan list
     */
    protected $description = 'Create a complete Entity Tool with database tables';

    protected $quailifiedEntityName = '';

    protected $tableMetaName ='';
    

    /**
     * Specify your Stub's location.
     */
    protected function getStub()
    {
        $publishedStub = base_path() . '/stubs/model.stub';
        if(is_file($publishedStub)){
            return $publishedStub;
        }
        else{
            return realpath(__DIR__.'/../stubs/model.stub');
        }
    }


    public function handle()
    {
        
        $quailifiedEntityName = Str::snake($this->getNameInput());
        $quailifiedEntityName = str_replace('\\','',$quailifiedEntityName);
        $this->quailifiedEntityName = $quailifiedEntityName;
        

        $tableMeta = $this->getTableMetaInput()
            ?$this->getTableMetaInput()
            :"{$quailifiedEntityName}_meta";

        $this->tableMetaName = Str::snake($tableMeta);

        if (!Schema::hasTable($this->tableMetaName)) {
            $this->tableMetaName = $tableMeta;
            Schema::create($this->tableMetaName, function($table) use($quailifiedEntityName){
                   $table->engine = 'InnoDB';
                   $table->bigIncrements('meta_id')->unsigned();
                   $table->bigInteger('model_reference')->unsigned();
                   $table->string('name', 255);
                   $table->string('value', 255)->nullable();
                   $table->unique(['model_reference','name']);
                   $table->timestamp('created_at')->useCurrent();
                   $table->timestamp('updated_at')->useCurrent();
                });
        }

        $result = parent::handle();


    }



    /**
     * The root location where your new file should 
     * be written to.
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Models';
    }


    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }


    protected function replaceClass($stub, $name)
    {
        $code = parent::replaceClass($stub,$name);

        $search = ['{{ table_meta }}','{{table_meta}}'];
        $code =  str_replace($search,$this->tableMetaName,$code);

        return $code;
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
            ['name', InputArgument::REQUIRED, 'The name of the class'],
            ['table_meta', InputArgument::OPTIONAL, 'The meta table if required. it will be created if not exists'],
        ];
    }



}
