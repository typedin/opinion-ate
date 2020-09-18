<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Dish;
use App\Models\Rating;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Restaurant::factory()
            ->has(
                Dish::factory()
                    ->hasComments(10)
                    ->hasRatings(10)
                ->count(10)
            )
            ->count(4)
            ->create();
    }
}
