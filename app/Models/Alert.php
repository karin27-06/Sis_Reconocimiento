<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';

    protected $fillable = [
        'idMovimiento',
        'descripcion',
        'fecha',
        'tipo',
    ];

    protected $casts = [
        'fecha' => 'date', // Para manejarlo como instancia de Carbon
        'tipo' => 'integer',
    ];

    /**
     * Relación con el movimiento
     */
    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movement::class, 'idMovimiento', 'id');
    }
}
