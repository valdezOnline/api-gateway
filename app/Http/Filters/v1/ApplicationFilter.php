<?php

namespace App\Http\Filters\v1;

class ApplicationFilter extends QueryFilter
{

    protected $sortable = [
        'name',
        'status',
        'createdBy' => 'created_by',
        'description' => 'description',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];
    public function name($value)
    {
        return $this->builder->where('name', $value);
    }
    public function status($value)
    {
        return $this->builder->where('status', $value);
    }
}