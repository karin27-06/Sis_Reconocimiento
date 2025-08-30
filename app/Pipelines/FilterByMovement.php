<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByMovement {
    private $search;

    public function __construct($search) {
        $this->search = $search;
    }

    public function __invoke(Builder $builder, Closure $next) {
        if (!empty($this->search)) {
            $searchLower = strtolower($this->search);

            $builder->where(function ($q) use ($searchLower) {
                // Buscar por ID (parcial)
                $q
                  // Buscar por nombre del espacio (parcial, case-insensitive)
                  ->orWhereHas('espacio', function ($q2) use ($searchLower) {
                      $q2->whereRaw('LOWER(name) LIKE ?', ['%' . $searchLower . '%']);
                  })
                  // Buscar por idTipo exacto o parcial
                  ->orWhere('idTipo', 'like', '%' . $searchLower . '%');
            });
        }

        return $next($builder);
    }
}
