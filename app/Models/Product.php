<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MedicalSpecialty;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'quantity',
        'valor',
        'image_path'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function medicalSpecialties()
    {
        return $this->belongsToMany(MedicalSpecialty::class);
    }
}
