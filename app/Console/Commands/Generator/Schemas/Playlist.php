<?php

namespace App\Console\Commands\Generator\Schemas;

use App\Console\Commands\Generator\Schema;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Schema
{
    public $id = "playlist";
    public $properties = [
        "id" => [
            "type" => "bigIncrements",
            "guarded" => true
        ],
        "name" => [
            "type" => "string",
            "length" => 64,
            "required" => true
        ]
    ];
    public $relations = [
        [
            "reference" => "file",
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
    public $softDeletes = true;

    public $policies = [
        "search" => ['user'],
        "index" => ['user'],
        "show" => ['user'],
        "store" => ['admin'],
        "update" => ['admin'],
        "delete" => ['admin'],
    ];

    public function resourceResponse(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
        if (isset($response['files'])) {
            $response['playtime'] = 0;
            /** @var File $file */
            foreach ($response['files'] as $file) {
                $response['playtime'] += $file->playtime;
            }

            $response['playtime'] = gmdate('i:s', $response['playtime']);

        }

    }


    public function processPivot(\Illuminate\Http\Request &$request, array &$data, $referenceId, $modelId = null): void
    {
        if ($modelId) {
            $position = 1;
            if ($latest = \App\FilePlaylist::all()->where('playlist_id', '=', $modelId)->sortByDesc('position')->first()) {
                $position = $latest->position + 1;
            }
            $data['position'] = $position;
        }
    }

    public function pivotResponse(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
        $response = [
            'id' => $response['id'],
            'name' => $response['name'],
            'position' => $response['pivot']['position'],
            'created_at' => $response['pivot']['created_at']
        ];
    }
}