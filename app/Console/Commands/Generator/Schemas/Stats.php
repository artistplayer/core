<?php

namespace App\Console\Commands\Generator\Schemas;

use App\Console\Commands\Generator\Schema;

class Stats extends Schema
{
    public $id = "stat";
    public $properties = [
        'file_id' => [
            'type' => 'integer'
        ],
        'playlist_id' => [
            'type' => 'integer'

        ],
        'position' => [
            "type" => "double",
            "length" => 15,
            "places" => 6
        ]
    ];
    public $relations = [
        [
            "reference" => "file",
            "type" => "one_to_one"
        ],
        [
            "reference" => "playlist",
            "type" => "one_to_one"
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


}