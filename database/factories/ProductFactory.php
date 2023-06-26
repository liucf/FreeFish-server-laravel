<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rootcategory;
use App\Models\Subcategory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 0, 9999),
            'originalPrice' => $this->faker->randomFloat(2, 0, 9999),
            'rootcategory_id' => Rootcategory::factory(),
            'subcategory_id' => Subcategory::factory(),
            'category_id' => Category::factory(),
            'status' => $this->faker->word,
            'description' => $this->faker->text,
        ];
    }
}
