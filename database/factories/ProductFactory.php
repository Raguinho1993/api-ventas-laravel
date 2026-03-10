<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

           'nombre'=> $this->faker->words(3,true),
            'descripcion' => $this->faker->paragraph,
            'codigo_de_barras'=> $this->faker->unique()->isbn13(),
            'precio'=> $this->faker->randomFloat(2, 10, 1000),
            'stock'=> $this->faker->numberBetween(0, 50),
            //'category_id'=> Category::factory(), 
        ];
    }
}
