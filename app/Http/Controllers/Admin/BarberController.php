<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarberController extends Controller
{
    // Menampilkan daftar semua barber
    public function index()
    {
        $barbers = Barber::with('user')->orderBy('nama')->get();

        return view('admin.barber.index', compact('barbers'));
    }

    // Menampilkan form edit nama/foto barber
    public function edit(Barber $barber)
    {
        return view('admin.barber.edit', compact('barber'));
    }

    // Menyimpan perubahan nama/foto barber
    public function update(Request $request, Barber $barber)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $barber->nama = $request->nama;

        // Nama akun login (users.name) ikut disamakan supaya konsisten
        // di mana pun nama barber ditampilkan (mis. halaman kerja barber).
        if ($barber->user) {
            $barber->user->update(['name' => $request->nama]);
        }

        if ($request->hasFile('foto')) {
            if ($barber->foto) {
                Storage::disk('public')->delete($barber->foto);
            }
            $barber->foto = $request->file('foto')->store('barbers', 'public');
        }

        $barber->save();

        return redirect()->route('admin.barber.index')
            ->with('success', 'Data barber berhasil diperbarui!');
    }

    // Toggle aktif/non-aktif - barber non-aktif tidak muncul sebagai
    // pilihan di form reservasi pelanggan.
    public function toggleStatus(Barber $barber)
    {
        $barber->update(['status_aktif' => ! $barber->status_aktif]);

        $pesan = $barber->status_aktif
            ? "Barber {$barber->nama} sekarang aktif."
            : "Barber {$barber->nama} sekarang non-aktif.";

        return redirect()->route('admin.barber.index')->with('success', $pesan);
    }
}
