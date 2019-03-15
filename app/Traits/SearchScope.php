<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait SearchScope
 * @package App\Traits
 * @author Maikel ten Voorde <info@signalize.nl>
 *
 *
 * Scope Example
 *
 * POSTDATA:
 * {
 *  "wheres": [
 *      {
 *          "name": [
 *              "Florade",
 *              {"value": "Florade", "operator": "eq"},
 *              {"operator": "like","value": "%F%"}
 *          ]
 *      },
 *      {
 *          "id": {
 *              "operator": ">",
 *              "value": 1
 *          },
 *          "name": "Florade"
 *      }
 *  ]
 * }
 *
 *
 */
trait SearchScope
{
    private $validOperators = [
        'eq', 'neq', 'lt', 'gt', 'lte', 'gte', '!regexp', '!like',
        '<', '>', '<=', '>=', '<>', '!=', '<=>', 'like', 'not like', 'regexp', 'not regexp'
    ];
    private $mappings = [
        'eq' => '=',
        'neq' => '!=',
        'lt' => '<',
        'gt' => '>',
        'lte' => '<=',
        'gte' => '>=',
        '!regexp' => 'not regexp',
        '!like' => 'not like'
    ];


    public function scopeSearch(Builder $builder, array $wheres)
    {
        foreach ($wheres as $where) {
            $builder->where(function (Builder $query) use ($where) {
                $operator = '=';
                foreach ($where as $property => $value) {
                    if (is_array($value)) {
                        if (isset($value[0])) {
                            if ($values = array_filter(array_map(function ($v) use ($query, $property) {
                                if (is_array($v)) {
                                    $op = $this->validOperator(isset($v['operator']) ? $v['operator'] : null);
                                    $val = isset($v['value']) ? $v['value'] : null;
                                    if ($val !== null) {
                                        if ($op === '=') {
                                            return $val;
                                        }
                                        $query->orWhere($property, $op, $val);
                                    }
                                    return false;
                                }
                                return $v;
                            }, $value), function ($v) {
                                return $v;
                            })) {
                                $values = array_unique($values);
                                if (count($values) === 1) {
                                    $query->orWhere($property, '=', $values);
                                } else {
                                    $query->orWhereIn($property, $values);
                                }
                            }
                            continue;
                        }
                        if (isset($value['operator'])) {
                            $operator = $this->validOperator($value['operator']);
                        }
                        $value = $value['value'];
                    }
                    $query->orWhere($property, $operator, $value);
                }
            }, 'AND');
        }

        return $builder;
    }


    private function validOperator($operator)
    {
        if (in_array($operator, $this->validOperators)) {
            return $this->mapOperator($operator);
        }
        return '=';
    }

    private function mapOperator($operator)
    {
        if (isset($this->mappings[$operator])) {
            return $this->mappings[$operator];
        }
        return $operator;
    }

}