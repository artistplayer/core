<?php
namespace App\Http\Controllers;
use App\File;
use App\Http\Resources\FilePlaylistResource;
use App\Playlist;
use Illuminate\Http\Request;
class FilePlaylistController extends Controller
{
    /**
     * @param Request $request
     * @param $referenceId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, $referenceId)
    {
        $this->authorize('index', Playlist::class);
        $order = $request->query->get('order', 'asc');
        $sort = strtolower($request->query->get('sort', 'id'));
        $limit = $request->query->get('limit', 100) ?: 100;
        if($limit > 100){
            $limit = 100;
        }
        $reference = File::findOrFail($referenceId);
        $collection = $reference->playlists()->orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        return FilePlaylistResource::collection($collection);
    }
    /**
     * @param Request $request
     * @param $referenceId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, $referenceId)
    {
        $this->authorize('store', Playlist::class);
        $data = $this->validate($request, [
			'position' => 'integer',
			'playlists' => 'array',
		]);
        $data = $this->getData($request, $data, $referenceId);
        $reference = File::findOrFail($referenceId);
        $reference->playlists()->attach($request->get('playlists'), $data);
        $attached = $reference->playlists()->whereIn('playlist_id', $request->get('playlists'))->get();
        return FilePlaylistResource::collection($attached);
    }
    /**
     * @param $referenceId
     * @param $modelId
     * @return FilePlaylistResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($referenceId, $modelId)
    {
        $reference = File::findOrFail($referenceId);
        $model = $reference->playlists()->findOrFail($modelId);
        $this->authorize('show', $model);
        return new FilePlaylistResource($model);
    }
    /**
     * @param Request $request
     * @param $referenceId
     * @param $modelId
     * @return FilePlaylistResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $referenceId, $modelId)
    {
        $reference = File::findOrFail($referenceId);
        $model = $reference->playlists()->findOrFail($modelId);
        $this->authorize('update', $model);
        $data = $this->validate($request, [
			'position' => 'integer',
		]);
        $data = $this->getData($request, $data, $referenceId, $modelId);
        $reference->playlists()->updateExistingPivot($modelId, $data);
        $model = $reference->playlists()->findOrFail($modelId);
        return new FilePlaylistResource($model);
    }
    /**
     * @param $referenceId
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($referenceId, $modelId)
    {
        $reference = File::findOrFail($referenceId);
        $model = $reference->playlists()->findOrFail($modelId);
        $this->authorize('delete', $model);
        $reference->playlists()->detach($modelId);
        return response()->json([], 204);
    }
    /**
     * @param Request $request
     * @param $referenceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function patch(Request $request, $referenceId)
    {
        var_dump($referenceId);
        var_dump($request->request->all());
        return response()->json([], 204);
    }
    /**
     * @param Request $request
     * @param array $data
     * @param integer $referenceId
     * @param integer|null $modelId
     * @return array
     */
    private function getData(Request $request, array $data, $referenceId, $modelId = null)
    {
        $__schema = new \App\Console\Commands\Generator\Schemas\Playlist();
        if ($modelId) {
            $position = 1;
            if ($latest = \App\FilePlaylist::all()->where('playlist_id', '=', $modelId)->sortByDesc('position')->first()) {
                $position = $latest->position + 1;
            }
            $data['position'] = $position;
        }
		unset($data['playlists']);
        return $data;
    }
}
