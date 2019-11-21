<?php
namespace App\Http\Controllers;
use App\Stat;
use App\Http\Resources\StatResource;
use Illuminate\Http\Request;
class StatController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $this->authorize('search', Stat::class);
        $wheres = $request->get('wheres', []);
        $result = Stat::search($wheres);
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
        return StatResource::collection($collection);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $this->authorize('index', Stat::class);
        $order = $request->query->get('order', 'asc');
        $sort = strtolower($request->query->get('sort', 'id'));
        $limit = $request->query->get('limit', 100) ?: 100;
        if($limit > 100){
            $limit = 100;
        }
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            foreach ($with as $w) {
                if (!method_exists(Stat::class, $w)) {
                    abort(400, 'Requested relation ['.$w.'] does not exist!');
                }
            }
            $collection = Stat::with($with)->orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        } else {
            $collection = Stat::orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        }
        return StatResource::collection($collection);
    }
    /**
     * @param Request $request
     * @return StatResource
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->authorize('store', Stat::class);
        $data = $this->validate($request, [
			'file_id' => 'integer',
			'playlist_id' => 'integer',
			'position' => 'numeric',
		]);
        $data = $this->getData($request, $data);
        $model = Stat::create($data);
        return new StatResource($model);
    }
    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int $modelId
     * @return StatResource
     * @throws \Exception
     */
    public function show(Request $request, ?int $modelId)
    {
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            foreach ($with as $w) {
                if (!method_exists(Stat::class, $w)) {
                    abort(400, 'Requested relation ['.$w.'] does not exist!');
                }
            }
            $model = Stat::with($with)->findOrFail($modelId);
        } else {
            $model = Stat::findOrFail($modelId);
        }
        $this->authorize('show', $model);
        return new StatResource($model);
    }
    /**
     * @param Request $request
     * @param int $modelId
     * @return StatResource
     * @throws \Exception
     */
    public function update(Request $request, ?int $modelId)
    {
        $model = Stat::findOrFail($modelId);
        $this->authorize('update', $model);
        $data = $this->validate($request, [
			'file_id' => 'integer',
			'playlist_id' => 'integer',
			'position' => 'numeric',
		]);
        $data = $this->getData($request, $data);
        $model->update($data);
        return new StatResource($model);
    }
    /**
     * @param Request $request
     * @param int $modelId
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Request $request, ?int $modelId)
    {
        $model = Stat::findOrFail($modelId);
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
