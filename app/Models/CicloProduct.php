<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CicloProduct extends Model
{
    protected $table = 'detalles_ciclo';

    protected $fillable = [
        'ciclo_id',
        'representante_id',
        'producto_id',
        'cantidad',
        'is_hospital'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function representative()
    {
        return $this->belongsTo(Representative::class, 'representante_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
