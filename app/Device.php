<?php

namespace App;

use Jenssegers\Model\Model;

class Device extends Model
{
    protected $visible = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $fillable = [];
    protected $casts = [
        "id" => "string",
        "label" => "string",
        "size" => "integer",
        "location" => "string"
    ];


    static function create(array $data)
    {
        return new static($data);
    }
}
