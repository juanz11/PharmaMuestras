<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCiclo extends Model
{
    protected $fillable = [
        'ciclo_id',
        'representante_id',
        'especialidad_id',
        'producto_id',
        'cantidad_por_doctor',
        'cantidad_total',
        'cantidad_con_porcentaje'
    ];

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function representante(): BelongsTo
    {
        return $this->belongsTo(Representative::class, 'representante_id');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class, 'especialidad_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
