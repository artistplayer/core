<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class StatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $response = parent::toArray($request);
        $model = $this;
        if (isset($response['files'])) {
            $response['files'] = StatFileResource::collection($this->files);
        }
        if(isset($response['pivot'])) {
            unset($response['pivot']['file_id'],$response['pivot']['stat_id']);
        }
        if (isset($response['playlists'])) {
            $response['playlists'] = StatPlaylistResource::collection($this->playlists);
        }
        if(isset($response['pivot'])) {
            unset($response['pivot']['playlist_id'],$response['pivot']['stat_id']);
        }
        return $response;
    }
    public function with($request)
    {
        $response = [];
        $model = $this;
        return $response;
    }
}
