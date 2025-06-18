<?php

namespace App\Filters;

class ProductFilter extends QueryFilter
{
    protected array $sortable = [
        'name',
        'description',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function name(string $value): void
    {
        $this->builder->where('name', 'LIKE', "%$value%");
    }

    public function description(string $value): void
    {
        $this->builder->where('description', 'LIKE', "%$value%");
    }

    public function createdAt(string $value): void
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            $this->builder->whereBetween('created_at', $dates);
        } else {
            $this->builder->whereDate('created_at', $value);
        }
    }

    public function updatedAt(string $value): void
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            $this->builder->whereBetween('updated_at', $dates);
        } else {
            $this->builder->whereDate('updated_at', $value);
        }
    }

    public function include(string $value): void
    {
        $this->builder->with(explode(',', $value));
    }
}