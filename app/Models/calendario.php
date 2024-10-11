<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class calendario extends Model
{
    use HasFactory;

    protected $table = 'calendarios';

    protected $fillable = [
        'titulo', 
        'descripcion', 
        'fecha_hora'
    ];

    public $timestamps = true; // Activa created_at y updated_at

    public function eventUserRelations()
    {
        return $this->hasMany(EventUserRelation::class, 'recordatorio_id');
    }
}
