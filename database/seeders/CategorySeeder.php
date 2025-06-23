<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['id' => 1, 'name' => 'Курка']);
        Category::create(['id' => 2, 'name' => 'МакМеню']);
        Category::create(['id' => 3, 'name' => 'Бургери McDonald\'s']);
        Category::create(['id' => 4, 'name' => 'Десерти']);
        Category::create(['id' => 5, 'name' => 'Напої McDonald\'s']);
        Category::create(['id' => 6, 'name' => 'Роли']);
        Category::create(['id' => 7, 'name' => 'Картопля']);

        Category::create(['id' => 8, 'name' => 'Соковита курка']);
        Category::create(['id' => 9, 'name' => 'Бургери KFC']);
        Category::create(['id' => 10, 'name' => 'Гарніри']);
        Category::create(['id' => 11, 'name' => 'Піца-Твістер']);
        Category::create(['id' => 12, 'name' => 'Дисерти']);
        Category::create(['id' => 13, 'name' => 'Напої KFC']);
        Category::create(['id' => 14, 'name' => 'Мілкшейк']);

        Category::create(['id' => 15, 'name' => 'Піца']);
        Category::create(['id' => 16, 'name' => 'Десерти']);
        Category::create(['id' => 17, 'name' => 'Напої ']);
        Category::create(['id' => 18, 'name' => 'Сайди']);
    }
}
