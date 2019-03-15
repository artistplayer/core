<?php
namespace App\Http\Controllers;
use App\Playlist;
use App\Http\Resources\PlaylistFileResource;
use App\File;
use Illuminate\Http\Request;
class PlaylistFileController extends Controller
{
    /**
     * @param Request $request
     * @param $referenceId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, $referenceId)
    {
        $this->authorize('index', File::class);
        $order = $request->query->get('order', 'asc');
        $sort = strtolower($request->query->get('sort', 'id'));
        $limit = $request->query->get('limit', 100) ?: 100;
        if($limit > 100){
            $limit = 100;
        }
        $reference = Playlist::findOrFail($referenceId);
        $collection = $reference->files()->orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        return PlaylistFileResource::collection($collection);
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
			'files' => 'array',
		]);
        $data = $this->getData($request, $data);
        $reference = Playlist::findOrFail($referenceId);
        $reference->files()->attach($request->get('files'), $data);
        $attached = $reference->files()->whereIn('file_id', $request->get('files'))->get();
        return PlaylistFileResource::collection($attached);
    }
    /**
     * @param $referenceId
     * @param $modelId
     * @return PlaylistFileResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($referenceId, $modelId)
    {
        $reference = Playlist::findOrFail($referenceId);
        $model = $reference->files()->findOrFail($modelId);
        $this->authorize('show', $model);
        return new PlaylistFileResource($model);
    }
    /**
     * @param Request $request
     * @param $referenceId
     * @param $modelId
     * @return PlaylistFileResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $referenceId, $modelId)
    {
        $reference = Playlist::findOrFail($referenceId);
        $model = $reference->files()->findOrFail($modelId);
        $this->authorize('update', $model);
        $data = $this->validate($request, [
			'position' => 'integer',
		]);
        $data = $this->getData($request, $data);
        $reference->files()->updateExistingPivot($modelId, $data);
        $model = $reference->files()->findOrFail($modelId);
        return new PlaylistFileResource($model);
    }
    /**
     * @param $referenceId
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($referenceId, $modelId)
    {
        $reference = Playlist::findOrFail($referenceId);
        $model = $reference->files()->findOrFail($modelId);
        $this->authorize('delete', $model);
        $reference->files()->detach($modelId);
        return response()->json([], 204);
    }
    /**
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function getData(Request $request, array $data)
    {
        $__schema = new \App\Console\Commands\Generator\Schemas\File();
        $data['position'] = 1;
		unset($data['files']);
        return $data;
    }
}
