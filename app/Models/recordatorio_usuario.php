<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class recordatorio_usuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'asignador_id',
        'recordatorio_id',
        'usuario_id',
    ];

    public $timestamps = true; // Activa created_at y updated_at

    public function calendario()
    {
        return $this->belongsTo(Calendario::class, 'recordatorio_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
