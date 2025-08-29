<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $table = 'spaces'; // nombre de la tabla

    protected $fillable = [
        'name',
        'description',
        'state',
    ];

    protected $casts = [
        'state' => 'boolean',
    ];
}
