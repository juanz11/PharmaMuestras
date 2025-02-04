<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Representative extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'zone'];

    public function doctors()
    {
        return $this->hasMany(RepresentativeDoctors::class);
    }
}
