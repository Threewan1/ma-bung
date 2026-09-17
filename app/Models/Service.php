<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'nama_layanan',
        'harga',
        'foto',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}