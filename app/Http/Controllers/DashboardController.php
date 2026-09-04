<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'pesanan' => Pesanan::with('detailPesanan.menu')->latest()->get(),
        ]);
    }

    public function updateStatus(Pesanan $pesanan)
    {
        request()->validate([
            'status_pesanan' => ['required', 'in:menunggu,diproses,selesai'],
        ]);

        $pesanan->update([
            'status_pesanan' => request('status_pesanan'),
        ]);

        return back();
    }
}