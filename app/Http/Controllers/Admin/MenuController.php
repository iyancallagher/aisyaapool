<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        return Inertia::render('Menu/Index', [
            'menu' => Menu::with('kategori')->get(),
            'kategori' => Kategori::all(),
        ]);
    }

    public function store(StoreMenuRequest $request)
    {
        $data = $request->validated();

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('menu', 'public');
        }

        Menu::create($data);

        return back()->with('success', 'Menu berhasil ditambahkan');
    }

    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $data = $request->validated();

        // Handle foto upload — ganti foto lama jika ada yang baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama dari storage jika ada
            if ($menu->foto) {
                Storage::disk('public')->delete($menu->foto);
            }
            $data['foto'] = $request->file('foto')->store('menu', 'public');
        } else {
            // Jangan overwrite foto lama jika tidak ada upload baru
            unset($data['foto']);
        }

        $menu->update($data);

        return back()->with('success', 'Menu berhasil diperbarui');
    }

    public function destroy(Menu $menu)
    {
        // Hapus foto dari storage saat menu dihapus
        if ($menu->foto) {
            Storage::disk('public')->delete($menu->foto);
        }

        $menu->delete();

        return back()->with('success', 'Menu berhasil dihapus');
    }
}