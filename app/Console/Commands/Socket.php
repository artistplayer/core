<?php

namespace App\Console\Commands;

use App\Console\Commands\Socket\OMXPlayer;
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
            if ($response = $this->process(json_decode($msg))) {
                foreach ($this->connections as $k => $connection) {
                    $connection->send(json_encode($response));
                }
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


    /**
     * @param $msg
     * @return mixed
     * @throws \Exception
     */
    private function process($msg)
    {
        if (isset($msg->channel)) {
            $channels = [
                'omx' => OMXPlayer::class,
//                'spotify' => Spotify::class
            ];
            if (!key_exists($msg->channel, $channels)) {
                throw new \Exception('Channel not exists!');
            }
            if (!isset($msg->method)) {
                throw new \Exception('Method not defined!');
            }
            $channel = $channels[$msg->channel];
            if ($msg->method === 'get') {
                return $channel::$$msg->property;
            }

            if (!method_exists($channel, $msg->property)) {
                throw new \Exception('Method ' . $channel . '::[' . $msg->property . ']() not exists!');
            }
            call_user_func_array($channel . '::' . $msg->property, isset($msg->value) ? is_array($msg->value) ? $msg->value : [$msg->value] : []);
            return $channel::$properties;
        }
    }

}