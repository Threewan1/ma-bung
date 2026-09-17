<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BarberSeeder extends Seeder
{
    /**
     * Seed 3 akun barber awal, password default sama ("barber123") untuk ketiganya.
     */
    public function run(): void
    {
        $barbers = [
            ['nama' => 'Iron', 'email' => 'iron@mabungbarbershop.com', 'foto' => 'barbers/iron.jpeg'],
            ['nama' => 'Rival', 'email' => 'rival@mabungbarbershop.com', 'foto' => 'barbers/rival.jpeg'],
            ['nama' => 'Amos', 'email' => 'amos@mabungbarbershop.com', 'foto' => 'barbers/amos.jpeg'],
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
                    'foto' => $data['foto'],
                    'status_aktif' => true,
                ]
            );
        }
    }
}
