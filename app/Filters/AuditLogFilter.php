<?php

namespace App\Filters;

class AuditLogFilter extends QueryFilter
{
    protected array $sortable = [
        'action',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function action(string $value): void
    {
        $this->builder->where('action', 'LIKE', "%$value%");
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