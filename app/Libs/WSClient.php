<?php

namespace App\Libs;

use Ratchet\Client\WebSocket;

class WSClient
{
    /** @var \Illuminate\Console\Command $cli */
    private $cli = null;
    private $debug = false;

    /** @var WebSocket */
    private $connection;
    private $listeners;

    public function __construct($host, \Illuminate\Console\Command $cli)
    {
        $this->cli = $cli;
        $this->debug = config('app.debug');


        $this->loop = \React\EventLoop\Factory::create();
        $connector = new \Ratchet\Client\Connector($this->loop);

        $connector($host)->then(function (WebSocket $conn) {
            $this->connection = $conn;
            if ($this->debug) {
                $this->cli->info('Connection established.');
            }
            $this->connection->on('message', function ($msg) {
                if ($this->debug) {
                    $this->cli->alert('Message received:');
                    $this->cli->info($msg);
                }
                $msg = json_decode($msg);
                foreach ($this->listeners as $listener) {
                    if ($msg->event === $listener['event']) {
                        if (
                            (!isset($msg->channel) && !isset($listener['channel'])) ||
                            (isset($msg->channel) && isset($listener['channel']) && $msg->channel === $listener['channel'])
                        ) {
                            $listener['callback']($msg->data, (isset($msg->sender) ? $msg->sender : null));
                        }
                    }
                }
            });

            if ($this->debug) {
                $conn->on('close', function ($code = null, $reason = null) {
                    $this->cli->alert('Connection closed');
                    $this->cli->error($code . '::' . $reason);
                });
            }

        }, function (\Exception $e) use ($host, $cli) {
            if ($this->debug) {
                $this->cli->alert('Error occurred!');
                $this->cli->error($e->getCode() . '::' . $e->getMessage());
                $this->cli->error($e->getTraceAsString());
            }
            sleep(5);
            $this->__construct($host, $cli);
        });
    }

    public function on($event, $callback)
    {
        if ($this->debug) {
            $this->cli->info('Add event listener: ' . $event);
        }
        $this->listeners[] = [
            'event' => $event,
            'callback' => $callback
        ];
    }

    /**
     * @param $channel
     * @param $callback
     */
    public function subscribe($channel, $callback)
    {
        $this->listeners[] = [
            'event' => 'message',
            'channel' => $channel,
            'callback' => $callback
        ];
    }

    public function publish($channel, $msg, $sender = null)
    {
        $msg = json_encode([
            'event' => 'message',
            'channel' => $channel,
            'data' => $msg,
            'sender' => $sender
        ]);
        if ($this->debug) {
            $this->cli->info('Publish message:');
            $this->cli->info($msg);
        }
        $this->connection->send($msg);
    }


    public function send($msg, $sender = null)
    {
        $msg = json_encode([
            'event' => 'message',
            'data' => $msg,
            'sender' => $sender
        ]);
        if ($this->debug) {
            $this->cli->info('Send message:');
            $this->cli->info($msg);
        }
        $this->connection->send($msg);
    }

    public function every($interval, $callable)
    {
        $this->loop->addPeriodicTimer($interval, function () use ($callable) {
            $callable($this);
        });
    }

    public function run()
    {
        $this->loop->run();
    }
}