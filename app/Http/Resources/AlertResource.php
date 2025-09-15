<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'idMovimiento' => $this->idMovimiento,
            'Movimiento' => $this->movimiento ? $this->movimiento->id : null, // Relación con movimientos
            'descripcion' => $this->descripcion,
            'fecha' => $this->fecha ? Carbon::parse($this->fecha)->format('d-m-Y') : null,
            'tipo' => $this->tipo,
            'tipoTexto' => $this->tipo == 1 ? 'Huella' : ($this->tipo == 2 ? 'Cara' : 'Desconocido'),
            'creacion' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'actualizacion' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }
}
