<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

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
        ]);

        // Simpan data layanan ke database
        Service::create($request->all());

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
        ]);

        // Update data layanan di database
        $layanan->update($request->all());

        // Redirect ke halaman daftar layanan dengan pesan sukses
        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil diupdate!');
    }

    // Menghapus data layanan dari database
    public function destroy(Service $layanan)
    {
        // Hapus data layanan
        $layanan->delete();

        // Redirect ke halaman daftar layanan dengan pesan sukses
        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil dihapus!');
    }
}