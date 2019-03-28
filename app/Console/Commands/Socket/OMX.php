<?php

namespace App\Console\Commands\Socket;

use App\File;
use App\Libs\WSClient;
use App\Playlist;

class OMX
{
    /** @var \Illuminate\Console\Command $cli */
    private $cli;
    private $client;

    private $volume = 100;
    private $muted = false;
    private $mode = 'normal';
    /** @var Playlist */
    private $playlist = null;
    /** @var File */
    private $file = null;

    private $status = 'Paused';
    private $position = 0;

    private $attempts = 0;

    public function __construct(\Illuminate\Console\Command $cli)
    {
        // Clear all running instances
        chmod("bin/omxcontrols", 0777);
        exec("kill $(ps aux | grep 'omxplayer' | awk '{print $2}')");
        $this->volume = $this->execute("get", "volume");


        $this->cli = $cli;
        $this->client = new WSClient('ws://localhost:9000', $cli);
        $this->client->on('connect', function ($data) {
            $this->client->publish('omx', $this->state());
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

    protected function setVolume($volume)
    {
        $this->execute("set", "volume", $volume / 100);
        $this->volume = $volume;
    }

    protected function setMuted($muted)
    {
        // @todo: Exec to mute
        $this->muted = $muted;
    }

    protected function setMode($mode)
    {
        // @todo: Exec to set mode
        $this->mode = $mode;
    }

    protected function setStatus($status)
    {
        $state = $this->execute('get', 'status');
        if ($state !== $status) {
            $this->execute("set", "pause");
        }
    }

    protected function setPosition($position)
    {
        $this->execute("set", "position", $position * 1000000);
        $this->position = $position * 1000000;
    }

    protected function setFile($fileId)
    {
        $file = File::find($fileId);

        $source = \Storage::disk('local')->path('public/' . $file->integrity_hash . '/media.' . $file->format);
        $this->execute("set", "play", $source . ' ' . $file->trimAtStart);
        $this->file = $file;
    }

    protected function setPlaylist($playlistId)
    {
        $playlist = Playlist::find($playlistId);
        $this->playlist = $playlist;
    }

    private function next()
    {
        $this->file = null;
        $this->position = 0;
        $this->status = 'Paused';
        if ($this->playlist) {

        }
        $this->client->publish('omx', $this->state());
    }


    private function state()
    {
        return [
            'volume' => $this->volume,
            'muted' => $this->muted,
            'mode' => $this->mode,
            'status' => $this->status,
            'position' => ((int)$this->position) / 1000000,
            'playlist' => $this->playlist,
            'file' => $this->file
        ];
    }

    private function execute($method, $property, $value = '')
    {
        $cmd = "bin/omxcontrols " . $method . " " . $property . " " . $value . ($method === 'set' ? " > /dev/null 2>&1" : "");
        @exec($cmd, $response);
        return join(PHP_EOL, $response);
    }

}