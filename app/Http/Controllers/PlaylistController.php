<?php
namespace App\Http\Controllers;
use App\Playlist;
use App\Http\Resources\PlaylistResource;
use Illuminate\Http\Request;
class PlaylistController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $this->authorize('search', Playlist::class);
        $wheres = $request->get('wheres', []);
        $result = Playlist::search($wheres);
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            $result->with($with);
        }
        $order = $request->query->get('order', 'asc');
        $sort = strtolower($request->query->get('sort', 'id'));
        $limit = $request->query->get('limit', 100) ?: 100;
        if($limit > 100){
            $limit = 100;
        }
        $collection = $result->orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        return PlaylistResource::collection($collection);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $this->authorize('index', Playlist::class);
        $order = $request->query->get('order', 'asc');
        $sort = strtolower($request->query->get('sort', 'id'));
        $limit = $request->query->get('limit', 100) ?: 100;
        if($limit > 100){
            $limit = 100;
        }
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            foreach ($with as $w) {
                if (!method_exists(Playlist::class, $w)) {
                    abort(400, 'Requested relation ['.$w.'] does not exist!');
                }
            }
            $collection = Playlist::with($with)->orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        } else {
            $collection = Playlist::orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        }
        return PlaylistResource::collection($collection);
    }
    /**
     * @param Request $request
     * @return PlaylistResource
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->authorize('store', Playlist::class);
        $data = $this->validate($request, [
			'id' => 'integer',
			'name' => 'string:required',
		]);
        $data = $this->getData($request, $data);
        $model = Playlist::create($data);
        return new PlaylistResource($model);
    }
    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int $modelId
     * @return PlaylistResource
     * @throws \Exception
     */
    public function show(Request $request, ?int $modelId)
    {
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            foreach ($with as $w) {
                if (!method_exists(Playlist::class, $w)) {
                    abort(400, 'Requested relation ['.$w.'] does not exist!');
                }
            }
            $model = Playlist::with($with)->findOrFail($modelId);
        } else {
            $model = Playlist::findOrFail($modelId);
        }
        $this->authorize('show', $model);
        return new PlaylistResource($model);
    }
    /**
     * @param Request $request
     * @param int $modelId
     * @return PlaylistResource
     * @throws \Exception
     */
    public function update(Request $request, ?int $modelId)
    {
        $model = Playlist::findOrFail($modelId);
        $this->authorize('update', $model);
        $data = $this->validate($request, [
			'id' => 'integer',
			'name' => 'string',
		]);
        $data = $this->getData($request, $data);
        $model->update($data);
        return new PlaylistResource($model);
    }
    /**
     * @param Request $request
     * @param int $modelId
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Request $request, ?int $modelId)
    {
        $model = Playlist::findOrFail($modelId);
        $this->authorize('delete', $model);
        $model->delete();
        return response()->json([], 204);
    }
    /**
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function getData(Request $request, array $data)
    {
        return $data;
    }
}
