<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movement extends Model
{
    protected $table = 'movimientos';
    use HasFactory;

    protected $fillable = [
        'idEspacio',
        'idTipo',
        'reconocido',
        'access',
        'error',
        'fechaEnvioESP32',
        'fechaRecepcion',
        'fechaReconocimiento',
    ];

    protected $casts = [
        'reconocido' => 'boolean',
        'access' => 'boolean',
    ];

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'idEspacio', 'id');
    }
    public function Alerts(): HasMany {
        return $this->hasMany(Alert::class, 'idMovimiento', 'id');
    }
    public function tieneRelaciones(): bool
    {
        //se agrega todas las relaciones que existan
        return $this->Alerts()->exists();
    }
}
