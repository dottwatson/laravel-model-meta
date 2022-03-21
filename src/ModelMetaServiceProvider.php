<?php
namespace Dottwatson\ModelMeta;


use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Dottwatson\ModelMeta\Commands\MakeModel;
use Dottwatson\ModelMeta\Commands\MakeModelMetaTable;

class ModelMetaServiceProvider extends BaseServiceProvider{

    /**
     * Bootstrap the package's services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadConfig();
        $this->registerCommands();


        $this->publishes([
            $this->packagePath('config/model-meta-types.php') => config_path('model-meta-types.php'),
            $this->packagePath('config/model-meta.php') => config_path('model-meta.php')
        ], 'model-meta-config');
    }

    /**
     * Load the package config.
     *
     * @return void
     */
    private function loadConfig()
    {
        $configPath = $this->packagePath('config/model-meta-types.php');

        $this->mergeConfigFrom($configPath, 'model-meta-types');
    }

    /**
     * Get the absolute path to some package resource.
     *
     * @param  string  $path  The relative path to the resource
     * @return string
     */
    private function packagePath($path)
    {
        return __DIR__."/$path";
    }

    /**
     * Register the package's artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->commands([
            MakeModel::class,
            MakeModelMetaTable::class
        ]);
    }


}