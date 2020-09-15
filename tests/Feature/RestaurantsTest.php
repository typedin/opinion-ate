<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RestaurantsTest extends TestCase
{
    use RefreshDatabase;

    private function decodeJsonFromResponse($response)
    {
        return json_decode($response->getContent(), true);
    }

    /** @test */
    public function a_non_registered_user_can_get_all_restaurants()
    {
        $test = Restaurant::factory()
            ->count(2)
            ->has(Dish::factory()->count(3))
            ->create();

        $response = $this->get('/api/v1/restaurants');

        $response->assertStatus(200);

        $this->assertArrayHasKey(
            "data",
            $this->decodeJsonFromResponse($response)
        );

        $this->assertEquals(
            2,
            count($this->decodeJsonFromResponse($response)["data"])
        );
    }
    
    /**
     * @test
     */
    public function a_guest_cannot_add_a_restaurant()
    {
        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];
        $route = "/api/v1/restaurants";
        $data = [
            "data" => [
                "type" => "restaurants",
                "attributes" => [
                    "name" => "Super Pasta",
                    "address" => "Somewhere over the rainbow"
                ]
            ]
        ];

        $this->assertCount(0, Restaurant::all());

        $response = $this->postJson($route, $data, $headers);

        $response->assertStatus(401);

        $this->assertCount(0, Restaurant::all());
    }
    /**
     * @test
     */
    public function a_user_can_add_a_restaurant()
    {
        $this->withoutExceptionHandling();
        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];
        $route = "/api/v1/restaurants";
        $data = [
            "data" => [
                "type" => "restaurants",
                "attributes" => [
                    "name" => "Super Pasta",
                    "address" => "Somewhere over the rainbow"
                ]
            ]
        ];

        $user = User::factory()->create();
        $this->assertCount(0, Restaurant::all());

        $response = $this->actingAs($user)->postJson($route, $data, $headers);

        $response->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'Super Pasta');

        $this->assertCount(1, Restaurant::all());
    }
}
