<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BarberSeeder extends Seeder
{
    /**
     * Seed 3 akun barber awal beserta data barber-nya. Password default
     * sama untuk ketiganya ("barber123") - sebaiknya diganti oleh
     * masing-masing barber setelah login pertama kali.
     */
    public function run(): void
    {
        $barbers = [
            ['nama' => 'Iron', 'email' => 'iron@mabungbarbershop.com'],
            ['nama' => 'Rival', 'email' => 'rival@mabungbarbershop.com'],
            ['nama' => 'Amos', 'email' => 'amos@mabungbarbershop.com'],
        ];

        foreach ($barbers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['nama'],
                    'password' => Hash::make('barber123'),
                    'role' => 'barber',
                    'email_verified_at' => now(),
                ]
            );

            Barber::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $data['nama'],
                    'status_aktif' => true,
                ]
            );
        }
    }
}
