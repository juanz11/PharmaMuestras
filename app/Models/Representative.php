<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ciclo;
use App\Models\DetalleCiclo;
use App\Models\RepresentativeDoctors;

class Representative extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'zone'];

    public function doctors()
    {
        return $this->hasMany(RepresentativeDoctors::class, 'representative_id');
    }

    public function cicloProducts()
    {
        return $this->hasMany(DetalleCiclo::class, 'representante_id');
    }

    public function getProductsForCycle(Ciclo $ciclo)
    {
        return DetalleCiclo::where('ciclo_id', $ciclo->id)
            ->where('representante_id', $this->id)
            ->with(['producto.medicalSpecialty'])
            ->get();
    }
}
