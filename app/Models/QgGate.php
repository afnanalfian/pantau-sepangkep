<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QgGate extends Model
{
    protected $fillable = ['nama', 'urutan'];

    public function uks()
    {
        return $this->hasMany(QgUk::class)->orderBy('urutan');
    }
}
