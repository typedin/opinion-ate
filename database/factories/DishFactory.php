<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DishFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dish::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker->addProvider(
            new \FakerRestaurant\Provider\en_US\Restaurant($this->faker)
        );
        return [
            "name" => $this->faker->foodName(),
            "user_id" => User::factory(),
            "restaurant_id" => Restaurant::factory()
        ];
    }
}
