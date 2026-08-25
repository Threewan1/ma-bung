<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'reservation_id',
        'nomor_antrian',
        'status_antrian',
        'waktu_dibuat',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}