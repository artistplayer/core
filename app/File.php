<?php
namespace App;
use App\Traits\PageScope;
use App\Traits\SearchScope;
use Illuminate\Database\Eloquent\Model as ExtendedClass;
use Illuminate\Database\Eloquent\SoftDeletes;
class File extends ExtendedClass
{
    use SearchScope;
    protected $visible = [];
    protected $hidden = [];
    protected $guarded = ['id'];
    protected $fillable = [];
    protected $casts = [
		'id' => 'integer',
		'integrity_hash' => 'string',
		'title' => 'string',
		'artist' => 'string',
		'filesize' => 'integer',
		'filepath' => 'string',
		'filename' => 'string',
		'format' => 'string',
		'thumbnail' => 'boolean',
		'mime_type' => 'string',
		'bitrate' => 'integer',
		'playtime' => 'double',
		'trimAtStart' => 'double',
		'trimAtEnd' => 'double',
		'created_at' => 'datetime',
		'updated_at' => 'datetime'
	];
    /**
	* @return \Illuminate\Database\Eloquent\Relations\belongsToMany
	*/
	public function playlists() {
		return $this->belongsToMany('App\Playlist')->using('App\FilePlaylist')->withPivot(['position','created_at','updated_at']);
	}
}
