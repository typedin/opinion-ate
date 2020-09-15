<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
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
    public function a_guest_cannot_create_a_restaurant()
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
    public function a_user_with_a_valid_token_can_create_a_restaurant()
    {
        $user = User::factory()->create();
        $token = $user->createToken(
            "create-restaurant",
            ["restaurant:create"]
        )->plainTextToken;
        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$token}"
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

        $response->assertStatus(201)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Super Pasta'
                 )
                 ->assertJsonPath(
                     'data.attributes.address',
                     'Somewhere over the rainbow'
                 );

        $this->assertCount(1, Restaurant::all());
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_update_a_restaurant()
    {
        $user = User::factory()->create();
        $token = $user->createToken(
            "update-restaurant",
            ["restaurant:update"]
        )->plainTextToken;
        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$token}"
        ];
        $route = "/api/v1/restaurants/1";
        $data = [
            "data" => [
                "type" => "restaurants",
                "id" => "1",
                "attributes" => [
                    "name" => "Very Super Pasta",
                    "address" => "Over the rainbow"
                ]
            ]
        ];
        Restaurant::factory()->create([
                "id" => 1,
                "name" => "Super Pasta",
                "address" => "Somewhere over the rainbow"
        ]);
        $this->assertEquals("Super Pasta", Restaurant::first()->name);

        $response = $this->patchJson($route, $data, $headers);

        $response->assertStatus(200)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Very Super Pasta'
                 )
                 ->assertJsonPath(
                     'data.attributes.address',
                     'Over the rainbow'
                 );

        $this->assertEquals("Very Super Pasta", Restaurant::first()->name);
        $this->assertEquals("Over the rainbow", Restaurant::first()->address);
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_delete_a_restaurant()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $data = [
            "data" => [
                "type" => "restaurants",
                "id" => "1",
            ]
        ];

        $headers = $this->headers($user);

        Restaurant::factory()->create([ "id" => 1 ]);

        $this->assertEquals(1, Restaurant::count());

        $response = $this->json("DELETE", "/api/v1/restaurants/1", $data, $headers);

        $this->assertEquals(0, Restaurant::count());
    }

    private function headers($user)
    {
        $token = $user->createToken(
            "delete-restaurant",
            ["restaurant:delete"]
        )->plainTextToken;

        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$token}"
        ];
    }
}
