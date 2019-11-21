<?php

namespace App\Services;

use Illuminate\Http\Request;

class InternalRequest
{
    //Make an internal request
    /**
     * @param $url ;
     * @param $action ;
     * @param array $data
     * @return \Symfony\Component\HttpFoundation\Response
     **/
    public static function request($url, $action, array $data = [])
    {
        // Create request
        $request = Request::create($url, $action, $data, [], [], [
            'HTTP_Accept' => 'application/json']);
        // Handle the require request and return the response;
        $response = app()->handle($request);
        if ($response->getStatusCode() >= 400) {
            throw new \Exception($response);
        }
        return $response;
    }
}