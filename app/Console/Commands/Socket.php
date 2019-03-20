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
        exec("chmod 0777 bin -Rf");
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

        $conn->send(json_encode([
            'method' => 'playstate',
            'value' => [
                'position' => join(PHP_EOL, $position),
                'duration' => join(PHP_EOL, $duration),
                'volume' => join(PHP_EOL, $volume),
                'source' => join(PHP_EOL, $source),
                'status' => join(PHP_EOL, $status)
            ]
        ]));
    }

    function onMessage(\Ratchet\ConnectionInterface $from, $msg)
    {
        try {
            $msg = json_decode($msg);
            switch (true) {
                case isset($msg->channel) && $msg->channel === 'omx':
                    if (!isset($msg->method)) {
                        return $from->send(json_encode([
                            'Method not defined!'
                        ]));
                    }
                    if (!isset($msg->property)) {
                        return $from->send(json_encode([
                            'Property not defined!'
                        ]));
                    }
                    exec("bin/omxcontrols " . $msg->method . " " . $msg->property . " " . (isset($msg->value) ? $msg->value : null), $response);
                    if ($response) {
                        return $from->send(json_encode([
                            $response
                        ]));
                    }
                    break;
                case isset($msg->channel) && $msg->channel === 'spotify':
                    $from->send(json_encode([
                        'Not implemented yet!'
                    ]));
                    break;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    function onClose(\Ratchet\ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
    }

    function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

}