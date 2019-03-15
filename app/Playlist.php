<?php
namespace App;
use App\Traits\PageScope;
use App\Traits\SearchScope;
use Illuminate\Database\Eloquent\Model as ExtendedClass;
use Illuminate\Database\Eloquent\SoftDeletes;
class Playlist extends ExtendedClass
{
    use SearchScope, SoftDeletes;
    protected $visible = [];
    protected $hidden = [];
    protected $guarded = ['id'];
    protected $fillable = [];
    protected $casts = [
		'id' => 'integer',
		'name' => 'string',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'deleted_at' => 'datetime'
	];
    /**
	* @return \Illuminate\Database\Eloquent\Relations\belongsToMany
	*/
	public function files() {
		return $this->belongsToMany('App\File')->using('App\FilePlaylist')->withPivot(['position','created_at','updated_at']);
	}
}
