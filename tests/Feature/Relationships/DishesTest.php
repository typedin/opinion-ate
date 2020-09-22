<?php

namespace Tests\Feature\Relationships;

use App\Models\Comment;
use App\Models\Dish;
use App\Models\Image;
use App\Models\Rating;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CanDecodeJson;
use Tests\Traits\HasHeader;

class DishesTest extends TestCase
{
    use RefreshDatabase, HasHeader, CanDecodeJson;

    /**
     * @test
     */
    public function a_visitor_can_show_a_dish_with_its_relationships()
    {
        Dish::factory()
            ->for(Restaurant::factory())
            ->has(Comment::factory())
            ->has(Image::factory()->count(2))
            ->has(Rating::factory()->count(10)->state(["value" => 5.00]))
            ->create([
                "id" => 1,
                "name" => "Jarret au Munster",
            ]);

        $response = $this->getJson(
            self::API_URL . "/dishes/1",
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(200);

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.id",
            "1"
        );

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.attributes.name",
            "Jarret au Munster"
        );

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.attributes.rating",
            "5.0"
        );

        $this->assertArrayHasKey(
            "relationships",
            $this->decodedJson($response, "data")
        );

        $this->assertArrayHasKey(
            "restaurant",
            $this->decodedJson($response, "data.relationships")
        );

        $this->assertArrayHasKey(
            "comments",
            $this->decodedJson($response, "data.relationships")
        );
    }
}
