<?php

namespace App\Routing;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;
use Illuminate\Routing\Router;

class ResourceRegistrar extends OriginalRegistrar
{
    // add data to the array
    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = [
        'index',
        'show',
        'store',
        'update',
        'destroy',
        'search',
        'sync'
    ];

    /**
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceSearch($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'search', $options);
        $uri = $this->getResourceUri($name . '/search');
        return $this->router->post($uri, $action);
    }

    /**
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceSync($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'sync', $options);
        $uri = $this->getResourceUri($name);
        return $this->router->put($uri, $action);
    }
}