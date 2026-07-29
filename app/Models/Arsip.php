<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $fillable = ['judul', 'kategori', 'keterangan', 'file_path', 'file_asli', 'diunggah_oleh'];
}
