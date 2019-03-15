<?php
namespace App;
use App\Traits\PageScope;
use App\Traits\SearchScope;
use Illuminate\Database\Eloquent\Relations\Pivot as ExtendedClass;
use Illuminate\Database\Eloquent\SoftDeletes;
class FilePlaylist extends ExtendedClass
{
    use SearchScope;
    protected $visible = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $fillable = [];
    protected $casts = [
		'position' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime'
	];
}
