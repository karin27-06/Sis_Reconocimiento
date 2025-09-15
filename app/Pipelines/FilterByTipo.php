<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByTipo
{
    public function __construct(
        private ?int $tipo = null,
        private ?string $fecha = null
    ) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        // Filtrar por tipo si está definido
        if (!is_null($this->tipo)) {
            $builder->where('tipo', $this->tipo);
        }

        // Filtrar por fecha exacta si está definida
        if (!is_null($this->fecha)) {
            $builder->whereDate('fecha', $this->fecha);
        }

        return $next($builder);
    }
}
