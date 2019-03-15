<?php

namespace App\Console\Commands;

use App\Console\Commands\Generator\Controller;
use App\Console\Commands\Generator\Migration;
use App\Console\Commands\Generator\Model;
use App\Console\Commands\Generator\Policies;
use App\Console\Commands\Generator\Resource;
use Illuminate\Console\Command;

class Generator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API Resources';

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
        $schemas = scandir(__DIR__ . '/Generator/Schemas');
        if ($schemas) {
            $generators = [
                new Migration(),
                new Model(),
                new Controller(),
                new Resource(),
                new Policies()
            ];

            /** @var Generator\Generator $generator */
            foreach ($generators as $generator) {
                foreach ($schemas as $schema) {
                    $class = 'App\\Console\\Commands\\Generator\\Schemas\\' . explode(".", $schema)[0];
                    if (class_exists($class)) {
                        $generator->load(new $class);
                    }
                }
                $generator->save();
            }
        }
    }
}
