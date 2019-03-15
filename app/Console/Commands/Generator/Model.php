<?php

namespace App\Console\Commands\Generator;


class Model extends Generator
{
    protected $reverseMigrations = [];
    protected $isPivot = false;

    public function save()
    {
        foreach ($this->files as $modelName => &$file) {
            $data = '';
            if (isset($this->reverseMigrations[$modelName])) {
                foreach ($this->reverseMigrations[$modelName] as $relation) {
                    if (!stristr($file['content'], 'function ' . $relation . 's()')) {
                        $data .= $this->createRelation((object)[
                            "type" => "many_to_one",
                            "reference" => strtolower($relation)
                        ], $modelName);
                    }
                }
            }
            $file['content'] = str_replace("{{reverseRelations}}", $data, $file['content']);
        }

        parent::save();
    }

    public function load($schema, $isPivot = false)
    {
        $this->isPivot = $isPivot;

        parent::load($schema);
    }

    /**
     * @throws \Exception
     */
    protected function generate()
    {
        if (!isset($this->schema->id)) {
            throw new \Exception('Invalid Schema for migration!');
        }

        $modelName = ucfirst($this->schema->id);

        $model = file_get_contents(__DIR__ . '/Templates/model.php.template');
        $model = str_replace([
            "{{type}}",
            "{{model}}",
            "{{relations}}",
            "{{hidden}}",
            "{{guarded}}",
            "{{casts}}",
            "{{softDeletes}}"
        ], [
            ($this->isPivot ? 'Relations\Pivot' : 'Model'),
            $modelName,
            $this->getRelations($modelName),
            $this->getColumns('hidden'),
            $this->getColumns('guarded'),
            $this->getCasts(),
            $this->schema->softDeletes ? ", SoftDeletes" : ''
        ], $model);

        $this->addFile($modelName, $model, 'app/' . $modelName . '.php');

    }

    private function getRelations($modelName)
    {
        if (!isset($this->schema->relations)) {
            return null;
        }
        $relations = '';
        $properties = $this->schema->relations;
        foreach ($properties as $relation) {
            $relations .= $this->createRelation($relation, $modelName);
        }
        return $relations;
    }

    private function createRelation($relation, $modelName)
    {
        $reference = ucfirst($relation['reference']);

        $type = "";
        $extras = "";
        switch ($relation['type']) {
            case "one_to_one":
                $type = "hasOne";
                break;
            case "one_to_many":
                $type = "hasMany";
                // Add for many_to_one relations
                if (!isset($this->reverseMigrations[$reference])) {
                    $this->reverseMigrations[$reference] = [];
                }
                $this->reverseMigrations[$reference][] = $modelName;
                break;
            case "many_to_one":
                $type = "belongsTo";
                break;
            case "many_to_many":
                $reference = ucfirst($relation['reference']);
                $tables = [$modelName, $reference];
                sort($tables);
                $relation['id'] = join('', $tables);

                $type = "belongsToMany";
                $extras .= "->using('App\\" . $relation['id'] . "')";
                $properties = [];
                if (isset($relation['properties'])) {
                    $properties = array_keys($relation['properties']);
                }
                if ($relation['timestamps']) {
                    $properties[] = 'created_at';
                    $properties[] = 'updated_at';
                }
                if ($relation['softDeletes']) {
                    $properties[] = 'deleted_at';
                }

                if ($properties) {
                    $extras .= "->withPivot(['" . join("','", $properties) . "'])";
                }

                $referrer = new self;
                $referrer->load((object)$relation, true);
                $referrer->save();

                break;
        }


        return "/**" . PHP_EOL .
            "\t* @return \Illuminate\Database\Eloquent\Relations\\" . $type . PHP_EOL .
            "\t*/" . PHP_EOL .
            "\tpublic function " . $relation['reference'] . 's() {' . PHP_EOL .
            "\t\treturn $" . "this->" . $type . "('App\\" . $reference . "')" . $extras . ";" .
            PHP_EOL . "\t}" . PHP_EOL . PHP_EOL;
    }

    private function getColumns($type)
    {
        $properties = $this->schema->properties;
        $properties = array_filter($properties, function ($value) use ($type) {
            return isset($value[$type]) && $value[$type];
        });

        if (!$properties) {
            return null;
        }

        return "'" . join("', '", array_keys($properties)) . "'";

    }

    private function getCasts()
    {
        $properties = (array)$this->schema->properties;

        if (isset($this->schema->timestamps) && $this->schema->timestamps) {
            $properties['created_at'] = ['type' => 'datetime'];
            $properties['updated_at'] = ['type' => 'datetime'];
        }
        if (isset($this->schema->softDeletes) && $this->schema->softDeletes) {
            $properties['deleted_at'] = ['type' => 'datetime'];
        }

        array_walk($properties, function (&$v, $k) {
            if (!isset($v['type'])) return false;
            $v = $k . "' => '" . $this->getType($v['type']);
        });
        $properties = array_filter($properties, function ($v) {
            return $v;
        });

        return PHP_EOL . "\t\t'" . join("'," . PHP_EOL . "\t\t'", $properties) . "'" . PHP_EOL . "\t";
    }
}