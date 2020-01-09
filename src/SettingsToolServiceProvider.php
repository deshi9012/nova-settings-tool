<?php

namespace Bakerkretzmar\NovaSettingsTool;

use Bakerkretzmar\NovaSettingsTool\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Bakerkretzmar\NovaSettingsTool\Commands\TestCommand;

class SettingsToolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $timestamp = date('Y_m_d_His', time());

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCommand::class
            ]);
        }
    
        $this->publishes([
            __DIR__.'/../config/nova-settings-tool.php' => config_path('nova-settings-tool.php'),
        ], 'nova-settings-tool');

        $this->publishes([
            __DIR__.'/database/migrations/create_settings_table.php' => database_path('migrations/'.$timestamp.'_create_settings_table.php'),
        ], 'migrations');
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'settings-tool');
        
        $this->loadRoutesFrom(__DIR__.'../routes/api.php');
        //        $this->app->booted(function () {
        //            $this->routes();
        //        });
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/settings-tool')
                ->group(__DIR__.'/../routes/api.php');
    }
}
