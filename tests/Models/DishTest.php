<?php

namespace Tests\Models;

use App\Models\Dish;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DishTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @see  https://adamwathan.me/2016/08/04/stubbing-eloquent-relations-for-faster-tests/
     */
    public function it_formats_its_ratings()
    {
        $dish = Dish::factory()->create();

        $ratings = new Collection([
            Rating::factory()->make(["value" => 1]),
            Rating::factory()->make(["value" => 2]),
            Rating::factory()->make(["value" => 3]),
            Rating::factory()->make(["value" => 4]),
        ]);
    
        $dish->ratings()->saveMany($ratings);

        $this->assertEquals(2.5, $dish->rating);
    }
}
