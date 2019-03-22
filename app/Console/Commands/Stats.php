<?php

namespace App\Console\Commands;


use App\File;
use WebSocket\Client;

class Stats extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Stats Service';


    public function handle()
    {
        $client = new Client('ws://127.0.0.1:9000');
        while (true) {
            try {
                $client->send(self::update());
            } catch (\Exception $e) {
            }
            sleep(10);
        }
    }


    static public function update()
    {
        @exec("bin/omxcontrols get position", $position);
        @exec("bin/omxcontrols get duration", $duration);
        @exec("bin/omxcontrols get volume", $volume);
        @exec("bin/omxcontrols get source", $source);
        @exec("bin/omxcontrols get status", $status);


        if ($source) {
            $integrity_hash = explode("/media", join(PHP_EOL, $source));
            $integrity_hash = explode("/", $integrity_hash[0]);
            $integrity_hash = end($integrity_hash);
            $file = File::all()->where('integrity_hash', '=', $integrity_hash)->first();
        }

        return json_encode([
            'method' => 'playstate',
            'value' => [
                'position' => isset($position) ? join(PHP_EOL, $position) : 0,
                'duration' => isset($duration) ? join(PHP_EOL, $duration) : 0,
                'volume' => isset($volume) ? join(PHP_EOL, $volume) : 100,
                'source' => isset($source) ? join(PHP_EOL, $source) : null,
                'status' => isset($status) ? join(PHP_EOL, $status) : null,
                'fileId' => (isset($file->id) ? $file->id : null)
            ]
        ]);
    }


}