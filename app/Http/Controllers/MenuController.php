<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Menu;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        return Inertia::render('Katalog', [
            'kategori' => Kategori::all(),
            'menu' => Menu::with('kategori')->get(),
        ]);
    }
}
