<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_kategori'])]
class Kategori extends Model
{
    protected $table = 'kategori';

    public function menu()
    {
        return $this->hasMany(Menu::class);
    }
}
