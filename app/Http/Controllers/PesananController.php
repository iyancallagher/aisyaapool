<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function store(StorePesananRequest $request)
    {
        $validated = $request->validated();

        $pesanan = DB::transaction(function () use ($validated) {
            $totalHarga = collect($validated['items'])->sum(
                fn ($item) => $item['harga'] * $item['jumlah']
            );

            $pesanan = Pesanan::create([
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'nomor_meja' => $validated['nomor_meja'],
                'status_pesanan' => 'menunggu',
                'total_harga' => $totalHarga,
            ]);

            foreach ($validated['items'] as $item) {
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['harga'] * $item['jumlah'],
                ]);
            }

            return $pesanan;
        });

        return redirect()->back()->with('pesanan', $pesanan->load('detailPesanan'));
    }
}