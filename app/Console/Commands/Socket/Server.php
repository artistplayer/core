<?php

namespace App\Console\Commands\Socket;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Server implements \Ratchet\MessageComponentInterface
{
    /** @var \Illuminate\Console\Command $cli */
    private $cli;

    /** @var array<Client> */
    private $clients = [];


    public function __construct(\Illuminate\Console\Command $cli)
    {
        $this->cli = $cli;
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

    /**
     * @param \Ratchet\ConnectionInterface $conn
     * @throws \Exception
     */
    function onOpen(\Ratchet\ConnectionInterface $conn)
    {
        $this->add($conn);

        /** @var Client $client */
        foreach ($this->clients as $client) {
            $client->send([
                'event' => 'connect',
                'data' => [
                    'id' => spl_object_id($conn)
                ],
                'message' => 'New Client Connected (#' . spl_object_id($conn) . ')'
            ], $this->find($conn));
        }
    }

    /**
     * @param \Ratchet\ConnectionInterface $from
     * @param $msg
     * @throws \Exception
     */
    function onMessage(\Ratchet\ConnectionInterface $from, $msg)
    {
        if (!$msg = json_decode($msg)) {
            throw new \Exception('Invalid message!');
        }


        $msg = (array)$msg;
        if (isset($msg['sender'])) {
            if (!isset($msg['block'])) {
                $msg['block'] = [];
            }
            $msg['block'][] = $msg['sender'];
        }

        /** @var Client $client */
        foreach ($this->clients as $client) {
            $client->send(array_merge($msg, [
                'event' => 'message',
                'sender' => spl_object_id($from)
            ]), $this->find($from));
        }
    }

    /**
     * @param \Ratchet\ConnectionInterface $conn
     * @throws \Exception
     */
    function onClose(\Ratchet\ConnectionInterface $conn)
    {
        /** @var Client $client */
        foreach ($this->clients as $client) {
            $client->send([
                'event' => 'close',
                'data' => [
                    'id' => spl_object_id($conn)
                ],
                'message' => 'Client Disconnected (#' . spl_object_id($conn) . ')'
            ], $this->find($conn));
        }

        $this->delete($conn);
    }

    /**
     * @param \Ratchet\ConnectionInterface $conn
     * @param \Exception $e
     * @throws \Exception
     */
    function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        $client = $this->find($conn);
        $client->send([
            'code' => $e->getCode(),
            'error' => $e->getMessage()
        ]);
    }


    /**
     * @var \Ratchet\ConnectionInterface $connection
     * @return Client
     * @throws
     */
    private function find($connection)
    {
        foreach ($this->clients as $client) {
            if ($client->is($connection)) {
                return $client;
            }
        }
        throw new \Exception('Unknown connection!');
    }

    /** @var \Ratchet\ConnectionInterface $connection */
    private function add($connection)
    {
        $this->clients[] = new Client($connection);
    }

    /** @var \Ratchet\ConnectionInterface $connection */
    private function delete($connection)
    {
        foreach ($this->clients as $k => $client) {
            if ($client->is($connection)) {
                unset($this->clients[$k]);
            }
        }
    }
}

class Client
{
    /** @var \Ratchet\ConnectionInterface $connection */
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function is($connection)
    {
        return $this->connection === $connection;
    }

    /**
     * @param array $msg
     * @param Client $sender
     */
    public function send($msg, $sender = null)
    {
        if (isset($msg['receivers']) && !in_array(spl_object_id($this->connection), $msg['receivers'])) {
            return;
        }

        if (isset($msg['block']) && in_array(spl_object_id($this->connection), $msg['block'])) {
            return;
        }

        $this->sendRaw(json_encode($msg), $sender);
    }

    /**
     * @param string $msg
     * @param Client $sender
     */
    public function sendRaw($msg, $sender = null)
    {
        if (is_null($sender) || !$sender->is($this->connection)) {
            $this->connection->send($msg);
        }
    }
}