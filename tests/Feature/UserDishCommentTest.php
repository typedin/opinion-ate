<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class UserDishCommentTest extends TestCase
{
    const MODEL = "comment";

    use RefreshDatabase, HasHeader;

    /**
     * @test
     */
    public function a_guest_cannot_comment_on_a_dish()
    {
        Restaurant::factory()->has(Dish::factory())->create();

        $this->assertCount(0, Dish::first()->comments) ;

        $response = $this->postJson(
            "/api/v1/comments",
            [
                "data" => [
                    "type" => "comments",
                    "attributes" => [
                        "body" => "Absolutely great dish.",
                    ]
                ]
            ],
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);
        $this->assertCount(0, Dish::first()->comments) ;
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_comment_on_a_dish()
    {
        $dish = Dish::factory()
            ->for(Restaurant::factory())
            ->create([
                "user_id" => 1
            ]);

        $this->assertCount(0, $dish->comments) ;

        $response = $this->postJson(
            "/api/v1/comments",
            [
                "data" => [
                    "type" => "comments",
                    "attributes" => [
                        "body" => "Absolutely great dish.",
                        "dish_id" => 1,
                        "user_id" => 1
                    ],
                ]
            ],
            $this->headersWithCredentials("post", 1)
        );

        $response->assertStatus(201);

        $this->assertCount(1, $dish->fresh()->comments) ;
        $this->assertEquals(1, $dish->user->id) ;
    }
}
