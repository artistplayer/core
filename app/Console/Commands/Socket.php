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


    private $connections = [];


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
        $this->connections[] = $conn;
        $conn->send(Stats::update());
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
                    exec("bin/omxcontrols " . $msg->method . " " . $msg->property . " " . (isset($msg->value) ? $msg->value : null) . ($msg->method === 'set' ? " > /dev/null 2>&1" : null), $response);
                    if ($response) {
                        $from->send(json_encode([
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

            foreach ($this->connections as $k => $connection) {
                $connection->send(Stats::update());
            }


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    function onClose(\Ratchet\ConnectionInterface $conn)
    {
        foreach ($this->connections as $k => $connection) {
            if ($connection === $conn) {
                unset($this->connections[$k]);
            }
        }
    }

    function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

}