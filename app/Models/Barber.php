<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    protected $fillable = [
        // Akun login (role 'barber') yang terhubung ke data ini
        'user_id',

        'nama',

        // Foto profil - opsional
        'foto',

        // Barber non-aktif tidak muncul sebagai pilihan saat reservasi
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
