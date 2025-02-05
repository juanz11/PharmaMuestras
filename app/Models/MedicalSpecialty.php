<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;

class MedicalSpecialty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function productos()
    {
        return $this->hasMany(Product::class, 'medical_specialty_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'medical_specialty_id');
    }
}
