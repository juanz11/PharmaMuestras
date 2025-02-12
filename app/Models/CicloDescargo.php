<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CicloDescargo extends Model
{
    protected $fillable = [
        'ciclo_id',
        'representante_id',
        'numero_descargo'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function representante()
    {
        return $this->belongsTo(Representative::class);
    }
}
