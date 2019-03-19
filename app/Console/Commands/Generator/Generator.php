<?php

namespace App\Console\Commands\Generator;

abstract class Generator
{
    /** @var Schema $schema */
    protected $schema;
    protected $files = [];
    protected $mapping = [
        'integer' => [
            'bigIncrements',
            'bigInteger',
            'integer',
            'increments',
            'mediumIncrements',
            'mediumInteger',
            'smallIncrements',
            'smallInteger',
            'tinyIncrements',
            'tinyInteger',
            'unsignedBigInteger',
            'unsignedInteger',
            'unsignedMediumInteger',
            'unsignedSmallInteger',
            'unsignedTinyInteger',
        ],
        'real' => [],
        'float' => ['float'],
        'double' => ['double'],
        'decimal' => ['decimal', 'unsignedDecimal'],
        'string' => [
            'uuid',
            'ipAddress',
            'macAddress',
            'char',
            'binary',
            'string',
            'text',
            'rememberToken',
            'lineString',
            'longText',
            'mediumText',
            'multiLineString',
            'geometry', 'geometryCollection', 'morphs', 'nullableMorphs', 'point', 'polygon', 'multiPoint', 'multiPolygon', 'enum' //Unknown
        ],
        'boolean' => ['boolean'],
        'object' => ['json', 'jsonb'],
        'array' => [],
        'collection' => [],
        'date' => ['date'],
        'datetime' => ['dateTime', 'dateTimeTz'],
        'datetime:Y' => ['year'],
        'datetime:H:i:s' => ['time', 'timeTz'],
        'timestamp' => ['timestamp', 'timestampTz']
    ];

    /**
     * @param \stdClass|Schema $schema
     */
    public function load($schema)
    {
        $this->schema = $schema;
        $this->generate();
    }

    public function addFile($model, $content, $location)
    {
        $methods = get_class_methods(Schema::class);
        foreach ($methods as $method) {
            $content = str_replace('{{' . $method . '}}', $this->getHook($method), $content);
        }

        $this->files[$model] = [
            "location" => $location,
            "content" => $content
        ];
    }

    public function save()
    {
        foreach ($this->files as $className => $file) {
            $file['content'] = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $file['content']);


            file_put_contents($file['location'], $file['content']);
        }
    }

    /**
     * @param $hook
     * @return string
     * @throws \ReflectionException
     */
    protected function getHook($hook)
    {
        if (!method_exists($this->schema, $hook)) {
            return null;
        }
        $method = new \ReflectionMethod($this->schema, $hook);
        if ($method->class === Schema::class) {
            return null;
        }

        $f = $method->getFileName();
        $start_line = $method->getStartLine();
        $end_line = $method->getEndLine();

        $source = file($f);
        $source = implode('', array_slice($source, 0, count($source)));
        $source = preg_split("/(\n|\r\n|\r)/", $source);

        $body = '';
        for ($i = $start_line; $i < $end_line; $i++) {
            $body .= "{$source[$i]}\n";
        }

        $schemaClass = $method->class;
        $body = str_replace('$this->', '$__schema->', $body);
        $body = str_replace('$this::', '\\' . $schemaClass, $body);

        return '$__schema = new \\' . $schemaClass . '();' . PHP_EOL . trim(trim($body), '{}');
    }

    protected function getType($type)
    {

        $type = strtolower($type);
        $types = array_filter($this->mapping, function ($v) use ($type) {
            return preg_grep("/" . $type . "/i", $v);
        });
        $keys = array_keys($types);
        return reset($keys);
    }

    protected function getValidator($type)
    {
        switch ($this->getType($type)) {
            case "float":
            case "double":
            case "decimal":
            case "timestamp":
                return 'numeric';
            case "object":
            case "collection":
                return 'array';
            case "real":
            case "datetime":
            case "datetime:Y":
            case "datetime:H:i:s":
                return 'string';
            default:
                return $this->getType($type);
        }
    }


    abstract protected function generate();
}