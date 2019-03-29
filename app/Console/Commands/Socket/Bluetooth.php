<?php

namespace App\Console\Commands\Socket;

use App\Libs\WSClient;

class Bluetooth
{
    /** @var \Illuminate\Console\Command $cli */
    private $cli;
    private $client;

    public function __construct(\Illuminate\Console\Command $cli)
    {
        // Clear all running instances

        $this->cli = $cli;
        $this->client = new WSClient('ws://localhost:9000', $cli);
        $this->client->on('connect', function ($data) {
            $this->client->publish('bluetooth', $this->scan());
        });

        $this->client->subscribe('bluetooth', function ($data, $sender) { // Listen to messages from a specific channel

        });


        $this->client->run();
    }

    private function scan()
    {
        $scan = $this->execute("hcitool scan");
        $devices = explode(PHP_EOL, substr($scan, strpos($scan, PHP_EOL) + 1));
        return array_map(function ($data) {
            $data = trim($data);
            $address = trim(substr($data, 0, strpos($data, "\t")));
            $label = trim(substr($data, strpos($data, $address) + strlen($address)));
            return [
                'address' => $address,
                'label' => $label
            ];
        }, $devices);

    }

    private function execute($cmd)
    {
        @exec($cmd, $response);
        return join(PHP_EOL, $response);
    }
}