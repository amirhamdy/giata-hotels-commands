<?php

namespace GiataCommands;

use Illuminate\Console\Command;

class HelloCommand extends Command
{
    protected $signature = 'hello';

    protected $description = 'Hello Command!';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->alert('hello, it is working!');
    }
}
