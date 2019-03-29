<?php

namespace App\Console\Commands\Socket;
use App\Libs\WSClient;

class Bluetooth
{
    /** @var \Illuminate\Console\Command $cli */
    private $cli;
    private $client;

    private $connected_devices;

    public function __construct(\Illuminate\Console\Command $cli)
    {
        // Clear all running instances
        $this->execute("hcitool scan");

        $this->cli = $cli;
        $this->client = new WSClient('ws://localhost:9000', $cli);
        $this->client->on('connect', function ($data) {
            $this->client->publish('bluetooth', $this->connected_devices);
        });

        $this->client->subscribe('omx', function ($data, $sender) { // Listen to messages from a specific channel
            foreach ($data as $property => $value) {
                if (property_exists($this, $property) && $this->{$property} !== $value) {
                    $method = 'set' . ucfirst($property);
                    if (method_exists($this, $method)) {
                        $this->{$method}($value);
                    }
                }
            }
            $this->client->publish('omx', $this->state(), $sender);
        });


        $this->client->every(0.5, function () {
            if ($this->file) {
                $position = $this->execute("get", "position");
                $status = $this->execute("get", "status");
                if ($this->position !== $position) {
                    $this->position = $position;
                }
                if ($this->status !== $status) {
                    $this->status = $status;
                }
                $this->client->publish('omx', [
                    'position' => ((int)$position) / 1000000,
                    'status' => $status ?: 'Paused'
                ]);

                if ($position >= ($this->file->playtime - 0.8) * 1000000) {
                    $this->next();
                }
            }
        });

        $this->client->run();
    }

    private function execute($cmd)
    {
//        $cmd = $command . $value . ($method === 'set' ? " > /dev/null 2>&1" : "");
        @exec($cmd, $response);
        return join(PHP_EOL, $response);
    }

}