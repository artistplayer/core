<?php

namespace App\Console\Commands\Generator;

class ReferringController extends Generator
{
//    protected $schema;

    protected function generate()
    {
        if (!isset($this->schema->model) || !isset($this->schema->reference)) {
            throw new \Exception('Invalid Schema for migration!');
        }

        $model = $this->schema->model;
        $reference = $this->schema->reference;
        $referring = $reference . $model;
        $collection = strtolower($model) . 's';
        $pivot = strtolower($model) . '_id';


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


        $insertValidation[] = "\t\t\t'" . strtolower($model) . "s' => 'array',";
        $cleanData[] = "\t\tunset(\$data['" . strtolower($model) . "s']);";


        $controller = file_get_contents(__DIR__ . '/Templates/referring_controller.php.template');
        $controller = str_replace([
            "{{referring}}",
            "{{reference}}",
            "{{model}}",
            "{{collection}}",
            "{{pivot}}",
            "{{insertValidation}}",
            "{{updateValidation}}",
            "{{cleanData}}"
        ], [
            $referring,
            $reference,
            $model,
            $collection,
            $pivot,
            "[" . PHP_EOL . join(PHP_EOL, $insertValidation) . PHP_EOL . "\t\t]",
            "[" . PHP_EOL . join(PHP_EOL, $updateValidation) . PHP_EOL . "\t\t]",
            PHP_EOL . join(PHP_EOL, $cleanData) . PHP_EOL
        ], $controller);

        $this->addFile($referring, $controller, 'app/Http/Controllers/' . $referring . 'Controller.php');
    }
}