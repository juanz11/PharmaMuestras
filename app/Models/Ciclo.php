<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Representative; // Added this line

class Ciclo extends Model
{
    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'status',
        'delivered_at',
        'porcentaje_hospitalario',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function detallesCiclo(): HasMany
    {
        return $this->hasMany(DetalleCiclo::class);
    }

    public function getRepresentativesWithProducts()
    {
        return Representative::whereHas('cicloProducts', function ($query) {
            $query->where('ciclo_id', $this->id);
        })->get();
    }
}
