<?php

namespace App\Console\Commands\Socket;

use App\File;

class OMXPlayer
{
    static $properties = [
        'volume' => null,
        'source' => null,
        'status' => null,
        'position' => null,
        'duration' => null
    ];


    public function __get($name)
    {
        if (is_null(static::$properties[$name])) {
            static::$properties[$name] = self::execute("get", $name);
        }
    }


    static function play($fileId)
    {
        $file = File::find($fileId);
        $source = \Storage::disk('local')->path('public/' . $file->integrity_hash . '/media.' . $file->format);
        self::execute("set", "play", $source . ' ' . $file->trimAtStart);
        static::$properties['position'] = $file->trimAtStart * 1000000;
        static::$properties['duration'] = $file->playTime * 1000000;
        static::$properties['source'] = $source;
        static::$properties['status'] = 'Playing';
    }

    static function pause()
    {
        self::execute("set", "pause");
        static::$properties['status'] = static::$properties['status'] === 'Paused' ? 'Playing' : 'Paused';
    }

    static function stop()
    {
        self::execute("set", "stop");
        static::$properties['status'] = 'Paused';
    }

    static function position($position)
    {
        self::execute("set", "position", $position);
        static::$properties['position'] = $position;
    }

    static function volume($volume)
    {
        self::execute("set", "volume", $volume);
        static::$properties['volume'] = $volume;
    }


    static private function execute($method, $property, $value = '')
    {
        $cmd = "bin/omxcontrols " . $method . " " . $property . " " . $value . ($method === 'set' ? " > /dev/null 2>&1" : "");
        echo $cmd;
        exec($cmd, $response);
        return $response;
    }
}



//if (!isset($msg->method)) {
//    return $from->send(json_encode([
//        'Method not defined!'
//    ]));
//}
//if (!isset($msg->property)) {
//    return $from->send(json_encode([
//        'Property not defined!'
//    ]));
//}
//
//
//if ($msg->property === 'play') {
//    if (!isset($msg->value)) {
//        return $from->send(json_encode([
//            'Value not defined!'
//        ]));
//    }
//    if (!isset($msg->value->file)) {
//        return $from->send(json_encode([
//            'File in value not defined!'
//        ]));
//    }
//    $file = File::find($msg->value->file);
//    $msg->value = \Storage::disk('local')->path('public/' . $file->integrity_hash . '/media.' . $file->format);
//    if ($file->trimAtStart) {
//        $msg->value .= " " . $file->trimAtStart;
//    }
//}
//
//
//echo "bin/omxcontrols " . $msg->method . " " . $msg->property . " " . (isset($msg->value) ? $msg->value : null) . ($msg->method === 'set' ? " > /dev/null 2>&1" : "");
//exec("bin/omxcontrols " . $msg->method . " " . $msg->property . " " . (isset($msg->value) ? $msg->value : null) . ($msg->method === 'set' ? " > /dev/null 2>&1" : null), $response);
//if ($response) {
//    $from->send(json_encode([
//        $response
//    ]));
//}