<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qna extends Model
{
    protected $fillable = ['nama', 'pertanyaan', 'jawaban', 'dijawab_oleh', 'dijawab_at', 'status'];

    protected $casts = ['dijawab_at' => 'datetime'];
}
