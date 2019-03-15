<?php

namespace App\Console\Commands\Generator;

class Controller extends Generator
{
    protected function generate()
    {
        if (!isset($this->schema->id)) {
            throw new \Exception('Invalid Schema for migration!');
        }

        $modelName = ucfirst($this->schema->id);


        $insertValidation = [];
        $updateValidation = [];
        $cleanData = [];
        foreach ($this->schema->properties as $property => $options) {
            $insertValidation[] = "\t\t\t'" . $property . "' => '" . $this->getType($options['type']) . (!empty($options['required']) ? ':required' : '') . "',";
            $updateValidation[] = "\t\t\t'" . $property . "' => '" . $this->getType($options['type']) . "',";

            if (isset($options['save']) && !$options['save']) {
                $cleanData[] = "\t\tunset(\$data['" . $property . "']);";
            }
        }

        $controller = file_get_contents(__DIR__ . '/Templates/controller.php.template');
        $controller = str_replace([
            "{{model}}",
            "{{insertValidation}}",
            "{{updateValidation}}",
            "{{cleanData}}"
        ], [
            $modelName,
            "[" . PHP_EOL . join(PHP_EOL, $insertValidation) . PHP_EOL . "\t\t]",
            "[" . PHP_EOL . join(PHP_EOL, $updateValidation) . PHP_EOL . "\t\t]",
            PHP_EOL . join(PHP_EOL, $cleanData) . PHP_EOL
        ], $controller);


        $this->addFile($modelName, $controller, 'app/Http/Controllers/' . $modelName . 'Controller.php');

        if (isset($this->schema->relations)) {
            $controller = new ReferringController();
            foreach ($this->schema->relations as $relation) {
                if ($relation['type'] === 'many_to_many') {
                    $reference = clone $this->schema;
                    $reference->model = $modelName;
                    $reference->reference = ucfirst($relation['reference']);
                    $reference->type = "many_to_many";
                    $reference->properties = $relation['properties'];
                    $reference->timestamps = $relation["timestamps"];
                    $reference->softDeletes = $relation["softDeletes"];

                    $controller->load($reference);
                    $controller->save();
                }
            }
        }
    }
}