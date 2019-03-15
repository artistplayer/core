<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

trait PageScope
{


    public function scopePage(Builder $builder, \Illuminate\Http\Request $request)
    {
        $order = $request->query->get('order', 'id');
        $sort = $request->query->get('sort', 'asc');
        $limit = $request->query->get('limit', 100) ?: 100;

        return $builder->orderBy($order, $sort)->paginate($limit)->appends($request->query->all());
    }
}