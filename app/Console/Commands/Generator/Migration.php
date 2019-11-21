<?php

namespace App\Console\Commands\Generator;


class Migration extends Generator
{
    private $isReference = false;

    public function __construct($isReference = false)
    {
        $this->isReference = $isReference;
    }

    protected function generate()
    {
        if (!isset($this->schema->id)) {
            throw new \Exception('Invalid Schema for migration!');
        }


        $filename = date("Y_m_d_000000") . "_create_" . $this->schema->id . "_table";
        $schema = "Schema::create('" . $this->schema->id . ($this->isReference ? null : "s") . "', function (Blueprint \$table) {" . PHP_EOL;
        foreach ($this->schema->properties as $property => $options) {
            if (isset($options['save']) && !$options['save']) continue;

            $schema .= "\t\t\t\$table->" . $options['type'] . "('" . $property . "'";
            if (!empty($options['length'])) {
                $schema .= ", " . $options['length'];
            }
            if (!empty($options['places'])) {
                $schema .= ", " . $options['places'];
            }
            $schema .= ")";
            if (!empty($options['nullable'])) {
                $schema .= "->nullable(true)";
            }
            if (!empty($options['unique'])) {
                $schema .= "->unique()";
            }
            if (isset($options['default'])) {
                $schema .= "->default(" . (!$options['default'] ? 'false' : ($options['default'] === true ? 'true' : (is_numeric($options['default']) ? $options['default'] : "'" . $options['default'] . "'"))) . ")";
            }
            $schema .= ";" . PHP_EOL;
        }
        $this->addRelations($schema, $this->schema);

        if ($this->schema->timestamps) {
            $schema .= "\t\t\t\$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));" . PHP_EOL;
            $schema .= "\t\t\t\$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));" . PHP_EOL;
        }
        if ($this->schema->softDeletes) {
            $schema .= "\t\t\t\$table->softDeletes();" . PHP_EOL;
        }
        $schema .= "\t\t});";


        $migrationName = explode("_", $this->schema->id);
        foreach ($migrationName as &$table) {
            $table = ucfirst($table);
        }
        $migrationName = implode("", $migrationName);

        $migration = @file_get_contents(__DIR__ . '/Templates/migration.php.template');
        $migration = str_replace([
            "{{model}}",
            "{{schema}}",
            "{{table}}",
            "{{migration}}"
        ], [
            ucfirst($this->schema->id),
            $schema,
            $this->schema->id . "s",
            $migrationName
        ], $migration);

        $this->addFile($filename, $migration, 'database/migrations/' . $filename . '.php');

    }

    private function addRelations(&$migration, $schema)
    {
        foreach ($schema->relations as $relation) {
            switch ($relation['type']) {
                case "many_to_many":
                    $this->createReferenceTable($schema->id, $relation);
                    break;
                case "one_to_one":
                case "one_to_many":
                    $migration .= "\t\t\t\$table->foreign('" . $relation['reference'] . "_id')->references('id')->on('" . $relation['reference'] . "s')->onDelete('cascade');" . PHP_EOL;
                    break;
            }
        }
    }

    private function createReferenceTable($table, $relation)
    {
        $reference = $relation['reference'];
        $tables = [$table, $reference];
        sort($tables);


        // Create Reference Migration File
        $migration = new Migration(true);
        $migration->load((object)array_merge($relation, [
            "id" => join("_", $tables),
            "properties" => array_merge([
                $table . "_id" => [
                    "type" => "integer"
                ],
                $reference . "_id" => [
                    "type" => "integer"
                ]
            ], $relation['properties']),
            "relations" => [
                [
                    "reference" => $table,
                    "type" => "one_to_one"
                ],
                [
                    "reference" => $reference,
                    "type" => "one_to_one"
                ]
            ]
        ]));
        $migration->save();
    }
}