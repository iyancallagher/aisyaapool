<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $makanan = Kategori::create(['nama_kategori' => 'Makanan']);
        $minuman = Kategori::create(['nama_kategori' => 'Minuman']);
        $snack   = Kategori::create(['nama_kategori' => 'Snack']);

        $menu = [
            // Makanan
            ['kategori_id' => $makanan->id, 'nama_menu' => 'Nasi Goreng Spesial', 'deskripsi' => 'Nasi goreng dengan telur, ayam suwir, dan kerupuk', 'harga' => 25000, 'status' => 'tersedia'],
            ['kategori_id' => $makanan->id, 'nama_menu' => 'Mie Goreng Aisya', 'deskripsi' => 'Mie goreng dengan sayuran dan bakso', 'harga' => 22000, 'status' => 'tersedia'],
            ['kategori_id' => $makanan->id, 'nama_menu' => 'Ayam Geprek Sambal Matah', 'deskripsi' => 'Ayam crispy dengan sambal matah khas', 'harga' => 28000, 'status' => 'tersedia'],
            ['kategori_id' => $makanan->id, 'nama_menu' => 'Nasi Ayam Bakar', 'deskripsi' => 'Nasi putih dengan ayam bakar bumbu kecap', 'harga' => 27000, 'status' => 'tersedia'],
            ['kategori_id' => $makanan->id, 'nama_menu' => 'Spaghetti Aglio Olio', 'deskripsi' => 'Spaghetti dengan bawang putih, cabai, dan minyak zaitun', 'harga' => 30000, 'status' => 'habis'],

            // Minuman
            ['kategori_id' => $minuman->id, 'nama_menu' => 'Es Kopi Susu Aisya', 'deskripsi' => 'Kopi susu gula aren khas rumahan', 'harga' => 18000, 'status' => 'tersedia'],
            ['kategori_id' => $minuman->id, 'nama_menu' => 'Kopi Hitam', 'deskripsi' => 'Kopi hitam single origin', 'harga' => 15000, 'status' => 'tersedia'],
            ['kategori_id' => $minuman->id, 'nama_menu' => 'Matcha Latte', 'deskripsi' => 'Matcha premium dengan susu segar', 'harga' => 20000, 'status' => 'tersedia'],
            ['kategori_id' => $minuman->id, 'nama_menu' => 'Es Teh Manis', 'deskripsi' => 'Teh manis dingin segar', 'harga' => 10000, 'status' => 'tersedia'],
            ['kategori_id' => $minuman->id, 'nama_menu' => 'Lemon Tea', 'deskripsi' => 'Teh dengan perasan lemon segar', 'harga' => 15000, 'status' => 'tersedia'],
            ['kategori_id' => $minuman->id, 'nama_menu' => 'Chocolate Milkshake', 'deskripsi' => 'Milkshake cokelat dengan whipped cream', 'harga' => 23000, 'status' => 'habis'],

            // Snack
            ['kategori_id' => $snack->id, 'nama_menu' => 'Kentang Goreng', 'deskripsi' => 'Kentang goreng renyah dengan saus sambal', 'harga' => 15000, 'status' => 'tersedia'],
            ['kategori_id' => $snack->id, 'nama_menu' => 'Pisang Goreng Keju', 'deskripsi' => 'Pisang goreng dengan taburan keju dan cokelat', 'harga' => 16000, 'status' => 'tersedia'],
            ['kategori_id' => $snack->id, 'nama_menu' => 'Roti Bakar Coklat Keju', 'deskripsi' => 'Roti bakar dengan cokelat dan keju leleh', 'harga' => 17000, 'status' => 'tersedia'],
            ['kategori_id' => $snack->id, 'nama_menu' => 'Cireng Bumbu Rujak', 'deskripsi' => 'Cireng renyah dengan bumbu rujak pedas manis', 'harga' => 12000, 'status' => 'tersedia'],
        ];

        foreach ($menu as $item) {
            Menu::create($item);
        }

        User::factory()->create([
            'name' => 'Admin Aisya',
            'email' => 'admin@aisyapoolside.com',
            'role' => 'admin',
        ]);
    }
}