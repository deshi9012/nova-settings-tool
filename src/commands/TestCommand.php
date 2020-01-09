<?php

namespace Bakerkretzmar\NovaSettingsTool\Commands;

use Illuminate\Console\Command;
use Request;
use Route;

class TestCommand extends Command
{
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'test:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple Test Command';

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
        $request = Request::create('/settings/manage', 'POST', []);
        return Route::dispatch($request)->getContent();
    }
}
