<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadController extends Controller
{
    public function __construct()
    {

    }

    public function store(Request $request)
    {
        $response = [];
        if ($request->hasfile('files')) {
            /** @var UploadedFile $file */
            foreach ($request->file('files') as $file) {
                $name = $file->getClientOriginalName();
                $file->move('/tmp', $name);
                $response[] = [
                    'integrity_hash' => md5('/tmp/' . $name),
                    'filepath' => '/tmp',
                    'filename' => $name,
                    'filesize' => filesize('/tmp/' . $name)
                ];
            }
        }
        return $response;
    }
}
