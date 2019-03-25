<?php

namespace App\Console\Commands\Generator\Schemas;

use App\Console\Commands\Generator\Schema;
use App\Http\Resources\PlaylistResource;
use Illuminate\Database\Eloquent\Model;

class File extends Schema
{
    public $id = "file";
    public $properties = [
        "id" => [
            "type" => "bigIncrements",
            "guarded" => true
        ],
        "integrity_hash" => [
            "type" => "string",
            "length" => 32,
            "unique" => true
        ],
        "title" => [
            "type" => "string",
            "length" => 64,
            "nullable" => true
        ],
        "artist" => [
            "type" => "string",
            "length" => 32,
            "nullable" => true
        ],
        "filesize" => [
            "type" => "mediumInteger",
            "min" => -8388608,
            "max" => 8388607
        ],
        "filepath" => [
            "type" => "string",
            "length" => 255,
            "required" => true,
            "save" => false
        ],
        "filename" => [
            "type" => "string",
            "length" => 255,
            "required" => true
        ],
        "format" => [
            "type" => "string",
            "length" => 8
        ],
        "thumbnail" => [
            "type" => "boolean",
            "default" => false
        ],
        "mime_type" => [
            "type" => "string",
            "length" => 32
        ],
        "bitrate" => [
            "type" => "mediumInteger",
            "min" => -8388608,
            "max" => 8388607
        ],
        "playtime" => [
            "type" => "double",
            "length" => 15,
            "places" => 6
        ],
        "trimAtStart" => [
            "type" => "double",
            "length" => 15,
            "places" => 6
        ],
        "trimAtEnd" => [
            "type" => "double",
            "length" => 15,
            "places" => 6
        ]
    ];
    public $relations = [
        [
            "reference" => "playlist",
            "type" => "many_to_many",
            "properties" => [
                "position" => [
                    "type" => "integer"
                ]
            ],
            "timestamps" => true,
            "softDeletes" => false
        ]
    ];

    public $timestamps = true;
    public $softDeletes = false;

    public $policies = [
        "search" => ['user'],
        "index" => ['user'],
        "show" => ['user'],
        "store" => ['admin'],
        "update" => ['admin'],
        "delete" => ['admin'],
    ];


    public function processResource(\Illuminate\Http\Request &$request, array &$data): void
    {
        if (isset($data['filepath']) && isset($data['filename'])) {
            $file = $data['filepath'] . "/" . $data['filename'];
            if (!file_exists($file)) {
                abort(400, "The file you requested cannot be found!");
            }
            $media_content = file_get_contents($file);
            $integrity_hash = md5($media_content);
            if (\App\File::where('integrity_hash', $integrity_hash)->first()) {
                abort(400, 'File already imported!');
            }
            try {
                ini_set('memory_limit', '512M');
                $getID3 = new \getID3();
                $info = $getID3->analyze($file);
                $data['integrity_hash'] = $integrity_hash;
                $data['filesize'] = $info['filesize'];
                $data['format'] = $info['fileformat'];
                $data['mime_type'] = $info['mime_type'];
                $data['bitrate'] = $info['bitrate'];
                $data['playtime'] = $info['playtime_seconds'];
                $data['filename'] = $info['filename'];
                $data['thumbnail'] = false;

                // Save Media File
                \Storage::disk('local')->put('public/' . $integrity_hash . '/media.' . $data['format'], $media_content);

                // Save Thumbnail
                if ($image = $this->encodeImage($info)) {
                    $content = explode('base64,', $image);
                    \Storage::disk('local')->put('public/' . $integrity_hash . '/image.jpg', base64_decode(trim($content[1])));
                    $data['thumbnail'] = true;
                }
            } catch (\Exception $exception) {
                abort(500, "Not able to analyse the requested file! (" . $exception->getMessage() . ")");
            }
        }
    }

    public function deleteResource(\Illuminate\Http\Request &$request, &$model): void
    {
        \Storage::disk('local')->deleteDirectory('public/' . $model->integrity_hash);
    }


    public function resourceResponse(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {

        $response['url'] = url(\Storage::disk('local')->url('public/' . $model->integrity_hash . '/media.' . $model->format));
        if (isset($response['thumbnail']) && $response['thumbnail'] === true) {
            $response['thumbnail'] = url(\Storage::disk('local')->url('public/' . $model->integrity_hash . '/image.jpg'));
        }
    }

    public function processPivot(\Illuminate\Http\Request &$request, array &$data, $referenceId, $modelId = null): void
    {
        $position = 1;
        if ($latest = \App\FilePlaylist::all()->where('playlist_id', '=', $referenceId)->sortByDesc('position')->first()) {
            $position = $latest->position + 1;
        }
        $data['position'] = $position;
    }

    public function pivotResponse(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
        $response = [
            'id' => $response['id'],
            'title' => $response['title'],
            'artist' => $response['artist'],
            'filename' => $response['filename'],
            'position' => $response['pivot']['position'],
            'created_at' => $response['pivot']['created_at']
        ];
    }

    public function encodeImage($info)
    {
        if (isset($info['comments']['picture']) && $image = $this->encodePicture($info['comments']['picture'])) {
            return $image;
        }
        if (isset($info['id3v1']['APIC']) && $image = $this->encodePicture($info['id3v1']['APIC'])) {
            return $image;
        }
        if (isset($info['id3v1']['PIC']) && $image = $this->encodePicture($info['id3v1']['PIC'])) {
            return $image;
        }
        if (isset($info['id3v2']['APIC']) && $image = $this->encodePicture($info['id3v1']['APIC'])) {
            return $image;
        }
        if (isset($info['id3v2']['PIC']) && $image = $this->encodePicture($info['id3v1']['PIC'])) {
            return $image;
        }
        return false;
    }

    private function encodePicture($picture)
    {
        if (isset($picture)) {
            return array_map(function ($img) {
                return array_map(function ($content, $property) {
                    if ($property === 'data') {
                        $content = 'data: image/jpeg; base64, ' . base64_encode($content);
                    }
                    return $content;
                }, $img, array_keys($img))[0];
            }, $picture)[0];
        }
        return false;
    }
}