<?php

namespace App\Console\Commands\Socket;

use App\File;

class OMXPlayer
{
    public $properties = [
        'volume' => 100,
        'source' => null,
        'status' => 'Paused',
        'position' => 0,
        'duration' => 0
    ];

    public function __construct()
    {
        foreach (['volume', 'source', 'status', 'position', 'duration'] as $property) {
            if ($value = $this->execute('get', $property)) {
                $this->properties[$property] = $value;
            }
        }

        $this->properties['mode'] = 'normal';
        $this->properties['muted'] = false;
        $this->properties['playlist'] = 1;


        if ($this->properties['source']) {
            $integrity_hash = explode("/media", join(PHP_EOL, $this->properties['source']));
            $integrity_hash = explode("/", $integrity_hash[0]);
            $integrity_hash = end($integrity_hash);
            $file = File::all()->where('integrity_hash', '=', $integrity_hash)->first();
            $this->properties['file'] = $file->id;
            $this->properties['position'] = $file->trimAtStart * 1000000;
            $this->properties['duration'] = $file->playTime * 1000000;
        }
    }


    public function play($fileId, $playlistId)
    {
        $file = File::find($fileId);
        $source = \Storage::disk('local')->path('public/' . $file->integrity_hash . '/media.' . $file->format);
        $this->execute("set", "play", $source . ' ' . $file->trimAtStart);

        $this->properties['file'] = $fileId;
        $this->properties['playlist'] = $playlistId;
        $this->properties['duration'] = $file->playTime * 1000000;
        $this->properties['duration'] = $file->playTime * 1000000;
        $this->properties['source'] = $source;
        $this->properties['status'] = 'Playing';
    }

    public function pause()
    {
        $this->execute("set", "pause");
        $this->properties['status'] = $this->properties['status'] === 'Paused' ? 'Playing' : 'Paused';
    }

    public function stop()
    {
        $this->execute("set", "stop");
        $this->properties['status'] = 'Paused';
    }

    public function position($position)
    {
        $this->execute("set", "position", $position);
        $this->properties['position'] = $position;
    }

    public function volume($volume)
    {
        $this->execute("set", "volume", $volume);
        $this->properties['volume'] = $volume;
    }

    private function execute($method, $property, $value = '')
    {
        $cmd = "bin/omxcontrols " . $method . " " . $property . " " . $value . ($method === 'set' ? " > /dev/null 2>&1" : "");
        @exec($cmd, $response);
        return $response;
    }


    public function __toString()
    {
        return json_encode($this->properties);
    }

}