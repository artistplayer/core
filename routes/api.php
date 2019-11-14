<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('v1')->group(function ($router) {
    Route::resources([
        'playlists' => 'PlaylistController',
        'playlists.files' => 'PlaylistFileController',

        'files' => 'FileController',
        'files.playlists' => 'FilePlaylistController',

        'devices' => 'DeviceController',

        'users' => 'UserController',

        'upload' => 'UploadController'
    ]);
});
