<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriController extends Controller
{
    public function index()
    {
        return Inertia::render('Kategori/Index', [
            'kategori' => Kategori::withCount('menu')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
        ]);

        Kategori::create($request->only('nama_kategori'));

        return back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
        ]);

        $kategori->update($request->only('nama_kategori'));

        return back()->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return back()->with('success', 'Kategori berhasil dihapus');
    }
}