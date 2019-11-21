<?php
namespace App;
use App\Traits\PageScope;
use App\Traits\SearchScope;
use Illuminate\Database\Eloquent\Model as ExtendedClass;
use Illuminate\Database\Eloquent\SoftDeletes;
class Stat extends ExtendedClass
{
    use SearchScope;
    protected $visible = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $fillable = [];
    protected $casts = [
		'file_id' => 'integer',
		'playlist_id' => 'integer',
		'position' => 'double',
		'created_at' => 'datetime',
		'updated_at' => 'datetime'
	];
    /**
	* @return \Illuminate\Database\Eloquent\Relations\hasOne
	*/
	public function files() {
		return $this->hasOne('App\File');
	}
/**
	* @return \Illuminate\Database\Eloquent\Relations\hasOne
	*/
	public function playlists() {
		return $this->hasOne('App\Playlist');
	}
}
