<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $fillable = [
        'nama_lokasi',
        'aktif'

    ];
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
