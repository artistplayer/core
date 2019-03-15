<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class FileResource extends JsonResource
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
        if (isset($response['playlists'])) {
            $response['playlists'] = FilePlaylistResource::collection($this->playlists);
        }
        if(isset($response['pivot'])) {
            unset($response['pivot']['playlist_id'],$response['pivot']['file_id']);
        }
        $__schema = new \App\Console\Commands\Generator\Schemas\File();
        return $response;
    }
    public function with($request)
    {
        $response = [];
        $model = $this;
        return $response;
    }
}
