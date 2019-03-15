<?php

namespace App\Console\Commands\Generator;

class Policies extends Generator
{
    protected function generate()
    {
        if (!isset($this->schema->id)) {
            throw new \Exception('Invalid Schema for migration!');
        }

        $modelName = ucfirst($this->schema->id);
        $policy = file_get_contents(__DIR__ . '/Templates/policy.php.template');
        $policy = str_replace("{{model}}", $modelName, $policy);


        $this->addFile($modelName, $policy, 'app/Policies/' . $modelName . 'Policy.php');
    }
}