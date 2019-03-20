<?php

namespace App\Console\Commands;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Socket extends \Illuminate\Console\Command implements \Ratchet\MessageComponentInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Socket Server';


    public function handle()
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this
                )
            ),
            9000
        );
        $server->run();
    }

    function onOpen(\Ratchet\ConnectionInterface $conn)
    {
        exec("bin/omxcontrols get position", $position);
        exec("bin/omxcontrols get duration", $duration);
        exec("bin/omxcontrols get volume", $volume);
        exec("bin/omxcontrols get source", $source);
        exec("bin/omxcontrols get status", $status);

        var_dump($position);
        var_dump($duration);
        var_dump($volume);
        var_dump($source);
        var_dump($status);
    }

    function onClose(\Ratchet\ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
    }

    function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    function onMessage(\Ratchet\ConnectionInterface $from, $msg)
    {
        $msg = json_decode($msg);


        // TODO: Implement onMessage() method.
    }


}