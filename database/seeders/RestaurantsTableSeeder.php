<?php

namespace Database\Seeders;

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
        $sushiPlace = Restaurant::create([
            'name' => 'Sushi Place',
            'address' => '123 Main Street'
        ]);

        $burgerPlace = Restaurant::create([
            'name' => 'Burger Place',
            'address' => '456 Other Street'
        ]);

        $sushiPlace->dishes()->createMany([
            ['name' => 'Volcano Roll', 'rating' => 3],
            ['name' => 'Salmon Nigiri', 'rating' => 4],
        ]);

        $burgerPlace->dishes()->createMany([
            ['name' => 'Barbecue Burger', 'rating' => 5],
            ['name' => 'Slider', 'rating' => 3],
        ]);
    }
}
