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
        $this->devices = new Collection();


        exec("lsblk -o label,mountpoint", $drives);
        $drives = array_filter($drives, function ($drive) {
            return stripos($drive, '/media');
        });

        $drives = array_map(function ($drive) {
            $drive = str_replace("  ", " ", trim($drive));
            $drive = explode(" ", $drive);
            if(count($drive) <= 1){
                array_unshift($drive, "NONAME");
            }
            return $drive;
        }, $drives);
        foreach ($drives as $drive) {
            $list = @scandir($drive[1]);
            if (count($list) > 2) {
                $this->devices->add(Device::create([
                    "id" => md5($drive[1]),
                    "label" => $drive[0],
                    "size" => exec("findmnt -bno size " . $drive[1]),
                    "location" => $drive[1]
                ]));
            }
        }

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
        $extensions = ['3gp', 'aa', 'aac', 'aax', 'act', 'aiff', 'amr', 'ape', 'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm', 'iklax', 'ivs', 'm4a', 'm4b', 'm4p', 'mmf', 'mp3', 'mpc', 'msv', 'nmf', 'nsf', 'ogg', 'oga', 'mogg', 'opus', 'ra', 'rm', 'raw', 'sln', 'tta', 'vox', 'wav', 'wma', 'wv', 'webm', '8svx'];
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

            $extension = explode(".", strtolower($file));
            $extension = end($extension);
            if (in_array($extension, $extensions)) {
                $fullpath = $directory . '/' . $file;
                $response[] = [
                    'integrity_hash' => md5($fullpath),
                    'filepath' => $directory,
                    'filename' => $file,
                    'filesize' => filesize($fullpath)
                ];
            }
        }
        return $response;
    }
}
