<?php

namespace GiataCommands;

use Illuminate\Support\ServiceProvider;

class GiataCommandsServiceProvider extends ServiceProvider
{

    protected $commands = [
        'GiataCommands\ProviderIDsCommand',
        'GiataCommands\InitialCommand',
        'GiataCommands\UpdateCommand',
        'GiataCommands\TranslateCommand',
        'GiataCommands\ImagesCommand',
    ];

    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/giata-commands.php' => config_path('giata-commands.php')]);
    }

    public function register()
    {
        $this->commands($this->commands);
    }
}
