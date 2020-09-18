<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class UserDishRatingTest extends TestCase
{
    const MODEL = "rating";

    use RefreshDatabase, HasHeader;

    /**
     * @test
     */
    public function a_guest_cannot_rate_a_dish()
    {
        Restaurant::factory()->has(Dish::factory())->create();

        $this->assertEquals(0, Dish::first()->rating) ;

        $response = $this->postJson(
            "/api/v1/ratings",
            [
                "data" => [
                    "type" => "ratings",
                    "attributes" => [
                        "value" => 5
                    ]
                ]
            ],
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);
        $this->assertEquals(0, Dish::first()->rating) ;
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_rate_a_dish()
    {
        Restaurant::factory()->has(Dish::factory())->create();

        $this->assertEquals(0, Dish::first()->rating) ;

        $response = $this->postJson(
            "/api/v1/ratings",
            [
                "data" => [
                    "type" => "ratings",
                    "attributes" => [
                        "value" => 5,
                        "dish_id" => 1
                    ]
                ]
            ],
            $this->headersWithCredentials("created")
        );
        $response->assertStatus(201);
        $this->assertEquals(5, Dish::first()->rating) ;
    }
}
