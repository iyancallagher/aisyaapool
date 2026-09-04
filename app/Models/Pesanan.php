<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_pelanggan', 'nomor_meja', 'status_pesanan', 'total_harga'])]
class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected function casts(): array
    {
        return [
            'total_harga' => 'decimal:2',
        ];
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class);
    }
}