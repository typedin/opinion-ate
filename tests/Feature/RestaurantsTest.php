<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class RestaurantsTest extends TestCase
{
    const MODEL = "restaurant";

    use RefreshDatabase, HasHeader;

    private function decodeJsonFromResponse($response)
    {
        return json_decode($response->getContent(), true);
    }

    /** @test */
    public function a_non_registered_user_can_index_all_restaurants()
    {
        Restaurant::factory()
            ->count(2)
            ->state(new Sequence(
                [
                    "name" => "First Restaurant",
                    "address" => "First Address"
                ],
                [
                    "name" => "Second Restaurant",
                    "address" => "Second Address"
                ],
            ))->create();

        $response = $this->getJson('/api/v1/restaurants', [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ]);

        $response->assertStatus(200);

        $response->assertJsonPath(
            'data.0.attributes.name',
            'First Restaurant'
        )->assertJsonPath(
            'data.0.attributes.address',
            'First Address'
        );

        $response ->assertJsonPath(
            'data.1.attributes.name',
            'Second Restaurant'
        )->assertJsonPath(
            'data.1.attributes.address',
            'Second Address'
        );
    }

    /**
     * @test
     */
    public function a_guest_cannot_create_a_restaurant()
    {
        $headersWithNoCredentials = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->assertCount(0, Restaurant::all());

        $response = $this->postJson(
            "/api/v1/restaurants",
            [],
            $headersWithNoCredentials
        );

        $response->assertStatus(401);
        $this->assertCount(0, Restaurant::all());
    }


    /**
     * @test
     */
    public function a_user_with_a_valid_token_can_create_a_restaurant()
    {
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

        $response = $this->postJson("/api/v1/restaurants", $data, $this->headers("patch"));

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
    public function a_user_with_a_token_can_patch_a_restaurant()
    {
        Restaurant::factory()->create([
            "id" => 1,
            "name" => "Super Pasta",
            "address" => "Somewhere over the rainbow"
        ]);

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

        $this->assertEquals("Super Pasta", Restaurant::first()->name);

        $response = $this->patchJson(
            "/api/v1/restaurants/1",
            $data,
            $this->headers("patch")
        );

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
        $data = [
            "data" => [
                "type" => "restaurants",
                "id" => "1",
            ]
        ];

        Restaurant::factory()->create([ "id" => 1 ]);

        $this->assertEquals(1, Restaurant::count());

        $response = $this->deleteJson(
            "/api/v1/restaurants/1",
            $data,
            $this->headers("delete")
        );

        $this->assertEquals(0, Restaurant::count());
    }
}
