<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';

    protected $fillable = [
        'idMovimientos',  // 👈 ahora es JSON
        'descripcion',
        'fecha',
        'tipo',
    ];

    protected $casts = [
        'idMovimientos' => 'array', // 👈 se convierte automáticamente en array
        'fecha' => 'date',
        'tipo' => 'integer',
    ];

    /**
     * Relación con los movimientos.
     * Devuelve una colección de Movement según los IDs guardados en idMovimientos.
     */
    public function movimientos()
    {
        return Movement::whereIn('id', $this->idMovimientos)->get();
    }
}
