<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::create(['id' => 1, 'name' => 'McDonald\'s', 'image' => '1.png']);
        Menu::create(['id' => 2, 'name' => 'KFC', 'image' => '2.png']);
        Menu::create(['id' => 3,'name' => 'Domino\'s Pizza', 'image' => '3.png']);
    }
}
