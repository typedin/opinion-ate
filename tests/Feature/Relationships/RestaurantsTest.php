<?php

namespace Tests\Feature\Relationships;

use App\Models\Dish;
use App\Models\Rating;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class RestaurantsTest extends TestCase
{
    use RefreshDatabase, HasHeader;
    
    public function setUp(): void
    {
        parent::setUp();

        Restaurant::factory()
            ->has(Dish::factory()
                    ->count(5)
                    ->state(new Sequence(
                        [ "name" => "AppleSauce" ],
                        [ "name" => "Chesseburger" ],
                        [ "name" => "Jarret au Munster"],
                    )))
            ->create([
                "id" => 1,
                "name" => "Super Pasta",
                "address" => "Somewhere over the rainbow"
            ]);
    }
    /**
     * @test
     */
    public function a_visitor_can_show_a_restaurant_with_its_relationships()
    {
        $response = $this->getJson(
            self::API_URL . "/restaurants/1",
            $this->headersWithNoCredentials()
        );

        $response->assertOk();

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.id",
            "1"
        );
        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.attributes.name",
            "Super Pasta"
        );

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.attributes.address",
            "Somewhere over the rainbow"
        );

        $this->assertArrayHasKey(
            "relationships",
            $this->decodedJson($response, "data")
        );
        $this->assertArrayHasKey(
            "self",
            $this->decodedJson($response, "data.relationships.dishes.links")
        );
        $this->assertArrayHasKey(
            "related",
            $this->decodedJson($response, "data.relationships.dishes.links")
        );
    }

    private function decodedJson(TestResponse $response, $path="data")
    {
        return Arr::get(json_decode($response->getContent(), true), $path);
    }
}
