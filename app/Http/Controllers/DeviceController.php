<?php

namespace App\Http\Controllers;

use App\Device;
use App\File;
use App\Http\Resources\DeviceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DeviceController extends Controller
{
    private $devices;

    public function __construct()
    {
        $this->devices = new Collection([
            Device::create([
                "id" => md5("/Users/maikeltenvoorde/Banden"),
                "label" => "USB MAIKEL",
                "size" => 15964184072,
                "location" => "/Users/maikeltenvoorde/Banden"
            ])
        ]);

    }


    public function index(Request $request)
    {
        return DeviceResource::collection($this->devices);
    }

    public function show(Request $request, ?string $id)
    {
        $device = $this->devices->where('id', $id)->first();
        $device->files = $this->readDir($device->location);

        $filter = File::search([
            [
                'filename' => array_map(function ($file) {
                    return $file['filename'];
                }, $device->files)
            ],
            [
                'filesize' => array_map(function ($file) {
                    return $file['filesize'];
                }, $device->files)
            ]
        ])->get();

        $device->files = array_filter($device->files, function ($file) use ($filter) {
            return !(count(array_filter($filter->toArray(), function ($f) use ($file) {
                    return $f['filesize'] === $file['filesize'];
                })) > 0);
        });
        return new DeviceResource($device);
    }

    public function destroy(Request $request, ?int $modelId)
    {
        //Eject Device
    }

    private function readDir($directory)
    {
        $response = [];
        $dir = @scandir($directory);
        $dir = array_filter($dir, function ($file) {
            return substr($file, 0, 1) !== '.';
        });
        foreach ($dir as $file) {
            if (is_dir($directory . '/' . $file)) {
                $response = array_merge($response, $this->readDir($directory . '/' . $file));
                continue;
            }
            $fullpath = $directory . '/' . $file;
            $response[] = [
                'integrity_hash' => md5($fullpath),
                'filepath' => $directory,
                'filename' => $file,
                'filesize' => filesize($fullpath)
            ];
        }
        return $response;
    }
}
