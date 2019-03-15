<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PlaylistFileResource extends JsonResource
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
        $__schema = new \App\Console\Commands\Generator\Schemas\File();
        $response = [
            'id' => $response['id'],
            'title' => $response['title'],
            'artist' => $response['artist'],
            'filename' => $response['filename'],
            'position' => $response['pivot']['position'],
            'created_at' => $response['pivot']['created_at']
        ];
        return $response;
    }
    public function with($request)
    {
        $response = [];
        $model = $this;
        return $response;
    }
}
