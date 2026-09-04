<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kategori_id', 'nama_menu', 'deskripsi', 'harga', 'foto', 'status'])]
class Menu extends Model
{
    protected $table = 'menu';

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class);
    }
}