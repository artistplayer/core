<?php
namespace App\Http\Controllers;
use App\File;
use App\Http\Resources\FileResource;
use Illuminate\Http\Request;
class FileController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $this->authorize('search', File::class);
        $wheres = $request->get('wheres', []);
        $result = File::search($wheres);
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
        return FileResource::collection($collection);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $this->authorize('index', File::class);
        $order = $request->query->get('order', 'asc');
        $sort = strtolower($request->query->get('sort', 'id'));
        $limit = $request->query->get('limit', 100) ?: 100;
        if($limit > 100){
            $limit = 100;
        }
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            foreach ($with as $w) {
                if (!method_exists(File::class, $w)) {
                    abort(400, 'Requested relation ['.$w.'] does not exist!');
                }
            }
            $collection = File::with($with)->orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        } else {
            $collection = File::orderBy($sort, $order)->paginate($limit)->appends($request->query->all());
        }
        return FileResource::collection($collection);
    }
    /**
     * @param Request $request
     * @return FileResource
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->authorize('store', File::class);
        $data = $this->validate($request, [
			'id' => 'integer',
			'integrity_hash' => 'string',
			'title' => 'string',
			'artist' => 'string',
			'filesize' => 'integer',
			'filepath' => 'string:required',
			'filename' => 'string:required',
			'format' => 'string',
			'thumbnail' => 'boolean',
			'mime_type' => 'string',
			'bitrate' => 'integer',
			'playtime' => 'double',
		]);
        $data = $this->getData($request, $data);
        $model = File::create($data);
        return new FileResource($model);
    }
    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int $modelId
     * @return FileResource
     * @throws \Exception
     */
    public function show(Request $request, ?int $modelId)
    {
        if($with = $request->get('with', [])){
            $with = !is_array($with) ? explode(",", $with) : $with;
            foreach ($with as $w) {
                if (!method_exists(File::class, $w)) {
                    abort(400, 'Requested relation ['.$w.'] does not exist!');
                }
            }
            $model = File::with($with)->findOrFail($modelId);
        } else {
            $model = File::findOrFail($modelId);
        }
        $this->authorize('show', $model);
        return new FileResource($model);
    }
    /**
     * @param Request $request
     * @param int $modelId
     * @return FileResource
     * @throws \Exception
     */
    public function update(Request $request, ?int $modelId)
    {
        $model = File::findOrFail($modelId);
        $this->authorize('update', $model);
        $data = $this->validate($request, [
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
		]);
        $data = $this->getData($request, $data);
        $model->update($data);
        return new FileResource($model);
    }
    /**
     * @param Request $request
     * @param int $modelId
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Request $request, ?int $modelId)
    {
        $model = File::findOrFail($modelId);
        $this->authorize('delete', $model);
        $model->delete();
        $__schema = new \App\Console\Commands\Generator\Schemas\File();
        \Storage::disk('local')->deleteDirectory('public/' . $model->integrity_hash);
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
        if (isset($data['filepath']) && isset($data['filename'])) {
            $file = $data['filepath'] . "/" . $data['filename'];
            if (!file_exists($file)) {
                abort(400, "The file you requested cannot be found!");
            }
            $media_content = file_get_contents($file);
            $integrity_hash = md5($media_content);
            if (\App\File::where('integrity_hash', $integrity_hash)->first()) {
                abort(400, 'File already imported!');
            }
            try {
                ini_set('memory_limit', '512M');
                $getID3 = new \getID3();
                $info = $getID3->analyze($file);
                $data['integrity_hash'] = $integrity_hash;
                $data['filesize'] = $info['filesize'];
                $data['format'] = $info['fileformat'];
                $data['mime_type'] = $info['mime_type'];
                $data['bitrate'] = $info['bitrate'];
                $data['playtime'] = $info['playtime_seconds'];
                $data['filename'] = $info['filename'];
                // Save Media File
                \Storage::disk('local')->put('public/' . $integrity_hash . '/media.' . $data['format'], $media_content);
                // Save Thumbnail
                if ($image = $__schema->encodeImage($info)) {
                    $content = explode('base64,', $image);
                    \Storage::disk('local')->put('public/' . $integrity_hash . '/image.jpg', base64_decode(trim($content[1])));
                    $data['thumbnail'] = true;
                }
            } catch (\Exception $exception) {
                abort(500, "Not able to analyse the requested file! (" . $exception->getMessage() . ")");
            }
        }
		unset($data['filepath']);
        return $data;
    }
}
