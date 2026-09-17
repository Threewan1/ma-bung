<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    // Menampilkan semua data layanan
    public function index()
    {
        // Ambil semua data layanan dari database
        $services = Service::all();
        // Kirim data ke view admin.layanan.index
        return view('admin.layanan.index', compact('services'));
    }

    // Menampilkan form tambah layanan baru
    public function create()
    {
        return view('admin.layanan.create');
    }

    // Menyimpan data layanan baru ke database
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'nama_layanan' => 'required', // wajib diisi
            'harga' => 'required|numeric', // wajib diisi dan harus angka
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nama_layanan', 'harga']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('layanan', 'public');
        }

        // Simpan data layanan ke database
        Service::create($data);

        // Redirect ke halaman daftar layanan dengan pesan sukses
        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil ditambahkan!');
    }

    // Menampilkan form edit layanan
    public function edit(Service $layanan)
    {
        // Kirim data layanan yang dipilih ke view edit
        return view('admin.layanan.edit', compact('layanan'));
    }

    // Menyimpan perubahan data layanan ke database
    public function update(Request $request, Service $layanan)
    {
        // Validasi input dari form
        $request->validate([
            'nama_layanan' => 'required',
            'harga' => 'required|numeric',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nama_layanan', 'harga']);

        if ($request->hasFile('foto')) {
            if ($layanan->foto) {
                Storage::disk('public')->delete($layanan->foto);
            }
            $data['foto'] = $request->file('foto')->store('layanan', 'public');
        }

        // Update data layanan di database
        $layanan->update($data);

        // Redirect ke halaman daftar layanan dengan pesan sukses
        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil diupdate!');
    }

    // Menghapus data layanan dari database
    public function destroy(Service $layanan)
    {
        if ($layanan->foto) {
            Storage::disk('public')->delete($layanan->foto);
        }

        // Hapus data layanan
        $layanan->delete();

        // Redirect ke halaman daftar layanan dengan pesan sukses
        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil dihapus!');
    }
}
