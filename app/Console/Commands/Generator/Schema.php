<?php

namespace App\Console\Commands\Generator;

use Illuminate\Database\Eloquent\Model;

abstract class Schema
{
    public $id;
    public $save = true;
    public $properties;
    public $relations;
    public $timestamps;
    public $softDeletes;

    /**
     * Process the posted data before it will be inserted or updated in the database.
     * This function will process the postdata for the Store and Update request.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $data
     * @return void
     */
    public function processResource(\Illuminate\Http\Request &$request, array &$data): void
    {
    }

    /**
     * Do some functions after the insert was finished.
     * This function will be executed after the new model was Stored.
     *
     * @param \Illuminate\Http\Request $request
     * @param $model
     * @return void
     */
    public function insertResource(\Illuminate\Http\Request &$request, &$model): void
    {
    }

    /**
     * Do some functions after the update was finished.
     * This function will be executed after the new model was Updated.
     *
     * @param \Illuminate\Http\Request $request
     * @param $model
     * @return void
     */
    public function updateResource(\Illuminate\Http\Request &$request, &$model): void
    {
    }

    /**
     * Do some functions after the delete was finished.
     * This function will be executed after the model has been Destroyed.
     *
     * @param \Illuminate\Http\Request $request
     * @param $model
     * @return void
     */
    public function deleteResource(\Illuminate\Http\Request &$request, &$model): void
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Model $model
     * @param array $response
     * @return void
     */
    public function resourceResponse(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Model $model
     * @param array $response
     * @return void
     */
    public function resourceWith(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
    }


    /**
     * Process the posted data before it will be inserted or updated in the database.
     * This function will process the postdata for the Store and Update request.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $data
     * @param integer $referenceId
     * @param integer|null $modelId
     * @return void
     */
    public function processPivot(\Illuminate\Http\Request &$request, array &$data, $referenceId, $modelId = null): void
    {
    }

    /**
     * Do some functions after the insert was finished.
     * This function will be executed after the new model was Stored.
     *
     * @param \Illuminate\Http\Request $request
     * @param $model
     * @return void
     */
    public function insertPivot(\Illuminate\Http\Request &$request, &$model): void
    {
    }


    /**
     * Do some functions after the update was finished.
     * This function will be executed after the new model was Updated.
     *
     * @param \Illuminate\Http\Request $request
     * @param $model
     * @return void
     */
    public function updatePivot(\Illuminate\Http\Request &$request, &$model): void
    {
    }

    /**
     * Do some functions after the delete was finished.
     * This function will be executed after the model has been Destroyed.
     *
     * @param \Illuminate\Http\Request $request
     * @param $model
     * @return void
     */
    public function deletePivot(\Illuminate\Http\Request &$request, &$model): void
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Model $model
     * @param array $response
     * @return void
     */
    public function pivotResponse(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Model $model
     * @param array $response
     * @return void
     */
    public function pivotWith(\Illuminate\Http\Request &$request, Model &$model, array &$response): void
    {
    }


}