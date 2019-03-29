<?php

namespace App\Console\Commands;

use App\Console\Commands\Socket\Bluetooth;
use App\Console\Commands\Socket\OMX;
use App\Console\Commands\Socket\Server;

class Socket extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Socket Service';


    private $services = [
        'server' => Server::class,
        'omx' => OMX::class,
        'bluetooth' => Bluetooth::class
    ];

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $type = $this->argument('type');
        if (isset($this->services[$type])) {
            return new $this->services[$type]($this);
        }

        $this->error('Undefined service-type!');
    }


}