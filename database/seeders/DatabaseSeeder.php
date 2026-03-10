<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $categorias = Category::factory(5)->create();
        
        Product::factory(20)->create([

        'category_id'=> fn() => $categorias->random()->id,
    # en la columna category_id lanza 20 veces de manera aleatoria para otorgar los 5 id
        ]);

        User::factory()->create([
           // 'name' => 'Test User',
            //'email' => 'test@example.com',
        ]);
    }
}
