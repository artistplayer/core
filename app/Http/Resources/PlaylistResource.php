<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PlaylistResource extends JsonResource
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
            $response['files'] = PlaylistFileResource::collection($this->files);
        }
        if(isset($response['pivot'])) {
            unset($response['pivot']['file_id'],$response['pivot']['playlist_id']);
        }
        $__schema = new \App\Console\Commands\Generator\Schemas\Playlist();
        if (isset($response['files'])) {
            $response['playtime'] = 0;
            /** @var File $file */
            foreach ($response['files'] as $file) {
                $response['playtime'] += $file->playtime;
            }
            $response['playtime'] = gmdate('i:s', $response['playtime']);
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
