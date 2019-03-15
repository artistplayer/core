<?php

namespace App\Console\Commands\Generator;

class Resource extends Generator
{
    protected function generate()
    {
        if (!isset($this->schema->id)) {
            throw new \Exception('Invalid Schema for migration!');
        }

        $modelName = ucfirst($this->schema->id);

        $resource = file_get_contents(__DIR__ . '/Templates/resource.php.template');
        $resource = str_replace(["{{model}}", "{{relations}}"], [$modelName, $this->getRelations($this->schema->relations)], $resource);

        $this->addFile($modelName, $resource, 'app/Http/Resources/' . $modelName . 'Resource.php');
    }


    private function getRelations($properties)
    {
        $relations = '';
        if ($properties) {
            foreach ($properties as $relation) {
                $relations .= $this->createRelation($relation);
            }
        }
        return $relations;
    }

    private function createRelation($relation)
    {
        $model = ucfirst($relation['reference']);
        $reference = $relation['reference'] . "s";


        if ($relation['type'] === 'many_to_many') {
            $this->createRefResource($model);
        }

        return "
        if (isset(\$response['" . $reference . "'])) {
            \$response['" . $reference . "'] = " . ucfirst($this->schema->id) . $model . "Resource::collection(\$this->" . $reference . ");
        }
        
        if(isset(\$response['pivot'])) {
            unset(\$response['pivot']['" . $relation['reference'] . "_id'],\$response['pivot']['" . $this->schema->id . "_id']);
        }
        
        ";
    }


    private function createRefResource($model)
    {
        $modelName = $model . ucfirst($this->schema->id);
        $resource = file_get_contents(__DIR__ . '/Templates/referring_resource.php.template');
        $resource = str_replace([
            "{{model}}",
            "{{relations}}",
            "{{pivotResponse}}",
            "{{pivotWith}}"
        ], [
            $modelName,
            null,
            $this->getHook('pivotResponse'),
            $this->getHook('pivotWith')
        ], $resource);

        $this->addFile($modelName, $resource, 'app/Http/Resources/' . $modelName . 'Resource.php');
    }
}