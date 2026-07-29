<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QgUk extends Model
{
    protected $fillable = ['qg_gate_id', 'nama', 'urutan'];

    public function gate()
    {
        return $this->belongsTo(QgGate::class, 'qg_gate_id');
    }

    public function aksiPreventifs()
    {
        return $this->hasMany(QgAksiPreventif::class)->orderBy('urutan');
    }
}
