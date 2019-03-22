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

    private $channels = [];


    public function handle()
    {
        exec("chmod 0777 bin -Rf");

        $this->channels['omx'] = new OMXPlayer();


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

        foreach ($this->channels as $channel) {
            $conn->send($channel);
        }
    }

    function onMessage(\Ratchet\ConnectionInterface $from, $msg)
    {
        try {
            if ($response = $this->process(json_decode($msg))) {
                foreach ($this->connections as $k => $connection) {
                    $connection->send($response);
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
            if (!key_exists($msg->channel, $this->channels)) {
                throw new \Exception('Channel not exists!');
            }
            if (!isset($msg->method)) {
                throw new \Exception('Method not defined!');
            }
            $channel = $this->channels[$msg->channel];
            if (!isset($channel->properties)) {
                throw new \Exception("Channel is invalid! 'Properties' property are missing!");
            }

            if ($msg->method === 'get') {
                if (!isset($channel->properties[$msg->property])) {
                    throw new \Exception('Property not found!');
                }
                return $channel->properties[$msg->property];
            }


            if (!method_exists($channel, $msg->property)) {
                throw new \Exception('Method ' . $channel . '::[' . $msg->property . ']() not exists!');
            }

            $parameters = [];
            if (isset($msg->value)) {
                $parameters = $msg->value;
                if (is_object($parameters)) {
                    $parameters = (array)$parameters;
                }
                if (!is_array($parameters)) {
                    $parameters = [$parameters];
                }
            }
            call_user_func_array([$channel, $msg->property], $parameters);
            $channel->update();

            return $channel;
        }
        return false;
    }

}