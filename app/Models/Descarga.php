<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Descarga extends Model
{
    protected $fillable = [
        'user_id',
        'archivo',
        'url',
        'estado',
        'archivo_local',
        'error_detalle',
        'procesado_en',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
