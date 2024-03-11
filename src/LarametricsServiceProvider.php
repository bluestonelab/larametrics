<?php

namespace Bluestone\Larametrics;

use Illuminate\Support\ServiceProvider;
use Bluestone\Larametrics\Commands\ScanCommand;

class LarametricsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanCommand::class,
            ]);
        }
    }
}
