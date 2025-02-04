<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Representative;
use App\Models\MedicalSpecialty;

class RepresentativeDoctors extends Model
{
    use HasFactory;

    protected $fillable = ['representative_id', 'medical_specialty_id', 'doctors_count'];

    public function representative()
    {
        return $this->belongsTo(Representative::class);
    }

    public function specialty()
    {
        return $this->belongsTo(MedicalSpecialty::class, 'medical_specialty_id');
    }
}
