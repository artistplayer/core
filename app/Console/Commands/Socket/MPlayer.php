<?php

namespace App\Console\Commands\Socket;

use App\File;
use App\Http\Controllers\StatController;
use App\Libs\WSClient;
use App\Playlist;
use App\Services\InternalRequest;
use App\Stat;

class MPlayer
{
    /** @var \Illuminate\Console\Command $cli */
    private $cli;
    private $client;

    /** @var State */
    private $state = null;
    private $instance = null;


    public function __construct(\Illuminate\Console\Command $cli)
    {
        $this->reset();

        $this->cli = $cli;
        $this->client = new WSClient('ws://localhost:9000', $cli);
        $this->client->on('connect', function ($data) {
            $this->client->publish('mplayer', $this->state);
        });

        // Listen to messages from a specific channel
        $this->client->subscribe('mplayer', function ($data, $sender) {
            foreach ($data as $property => $value) {
                $this->set($property, $value);
            }
            $this->client->publish('mplayer', $this->state, $sender);
        });


        $this->client->every(.5, function (WSClient $wsClient) {
            if ($this->state->file && $this->state->status === 'Playing') {
                $this->state->position += .5;
                $this->client->publish('mplayer', [
                    'status' => $this->state->status,
                    'position' => $this->state->position,
                ]);

                // Execute every 30 seconds of playing
                if ((round($this->state->position) === $this->state->position) && !($this->state->position % 30)) {
                    try {
                        InternalRequest::request('/api/v1/stats', 'POST', [
                            'file_id' => $this->state->file->id,
                            'playlist_id' => $this->state->playlist ? $this->state->playlist->id : null,
                            'position' => $this->state->position
                        ]);
                    } catch (\Exception $e) {
                    }
                }


                // Stop on end of playing
                if ($this->state->position >= $this->state->file->playtime) {
                    $this->reset();
                    $this->client->publish('mplayer', $this->state);
                }

            }
        });

        $this->client->run();
    }

    private function reset()
    {
        exec("kill $(ps aux | grep 'mplayer -slave' | awk '{print $2}')");
        $this->state = new State();
    }


    private function set($property, $value)
    {
        switch ($property) {
            case "file":
                $this->setFile($value);
                break;
            case "playlist":
                $this->setPlaylist($value);
                break;
            case "status":
                $this->setStatus($value);
                break;
            case "position":
                $this->setPosition($value);
                break;
            default:
                $this->state->{$property} = $value;
                break;
        }
    }

    private function setFile($id)
    {
        $this->reset();
        if ($this->state->file = File::find($id)) {
            $source = \Storage::disk('local')->path('public/' . $this->state->file->integrity_hash . '/media.' . $this->state->file->format);
            $this->instance = popen("mplayer -slave -quiet -idle " . $source . " > /dev/null", "w");

            $this->state->position = 0;
            $this->state->status = 'Playing';

            $this->setFX(true);

            $this->client->publish('mplayer', [
                'status' => $this->state->status,
                'position' => $this->state->position,
            ]);

            return;
        }

        $this->client->publish('error', 'File not found!');

    }

    private function setPlaylist($id)
    {
        $playlist = Playlist::find($id);
        $this->state->playlist = $playlist;
    }

    private function setStatus($status)
    {
        if ($this->instance) {
            fputs($this->instance, "pause\n");
            $this->state->status = $status;

            $this->setFX($status === 'Playing');

            $this->client->publish('mplayer', [
                'status' => $this->state->status,
                'position' => $this->state->position,
            ]);

        }
    }

    private function setPosition($position)
    {
        if ($this->instance) {
            fputs($this->instance, "seek " . $position . " 2\n");
            $this->state->position = (int)$position;
            if ($this->state->status === 'Paused') {
                fputs($this->instance, "pause\n");
            }
        }
    }

    private function setFX($enabled)
    {
        $path = dirname(dirname(dirname(__DIR__))) . "/Libs/Midi/";
        exec("aplaymidi --port=20:0 " . $path . ($enabled ? 'on' : 'off') . ".mid > /dev/null 2>/dev/null &");
    }
}

class State
{
    /** @var File */
    public $file = null;
    /** @var Playlist */
    public $playlist = null;
    public $status = 'Paused';
    public $position = 0;
}
