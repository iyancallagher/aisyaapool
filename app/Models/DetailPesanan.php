<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pesanan_id', 'menu_id', 'jumlah', 'subtotal'])]
class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
        ];
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}