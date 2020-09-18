<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DishesTest extends TestCase
{
    use RefreshDatabase;

    private function decodeJsonFromResponse($response)
    {
        return json_decode($response->getContent(), true);
    }

    /**
     * @test
     */
    public function a_non_registered_user_can_index_all_dishes()
    {
        Dish::factory()
            ->for(Restaurant::factory())
            ->count(3)
            ->state(new Sequence(
                [
                    "name" => "Chesseburger",
                    "rating" => 3.42
                ],
                [
                    "name" => "AppleSauce",
                    "rating" => 4.42
                ],
                [
                    "name" => "Jarret au Munster",
                    "rating" => 5.00
                ],
            ))->create();

        $response = $this->getJson("api/v1/dishes", [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ]);
        $response->assertStatus(200);

        $this->assertEquals(
            3,
            count($this->decodeJsonFromResponse($response)["data"])
        );
    }

    /**
     * @test
     */
    public function a_non_registered_user_can_read_a_dish()
    {
        Dish::factory()
            ->for(Restaurant::factory())
            ->create([
                "id" => 1,
                "name" => "Jarret au Munster",
                "rating" => 4.42
            ]);

        $response = $this->getJson(
            "api/v1/dishes/1",
            [
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ]
        );
        
        $response->assertStatus(200)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Jarret au Munster',
                 )->assertJsonPath(
                     "data.attributes.rating",
                     4.42
                 );
    }

    /**
     * @test
     */
    public function a_guest_cannot_create_a_dish_for_a_restaurant()
    {
        $headersWithNoCredentials = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];
        $data = [
            "data" => [
                "type" => "dishes",
                "attributes" => [
                    "name" => "Jarret au Munster",
                    "rating" => 4.42,
                    "restaurant_id" => 1
                ],
            ]
        ];
        Restaurant::factory()->create(["id" => 1]);
        $this->assertCount(0, Restaurant::first()->dishes);

        $response = $this->postJson(
            "/api/v1/dishes",
            $data,
            $headersWithNoCredentials
        );

        $response->assertStatus(401);
        $this->assertCount(0, Restaurant::first()->dishes);
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_can_create_a_dish_for_a_restaurant()
    {
        $this->withoutExceptionHandling();
        $data = [
            "data" => [
                "type" => "dishes",
                "attributes" => [
                    "name" => "Jarret au Munster",
                    "rating" => 4.42,
                    "restaurant_id" => 1
                ],
                "relationships" => [
                    "restaurants" => [
                        "data" => [
                            "type" => "restaurants",
                            "id" => "1"
                        ]
                    ]
                ]
            ]
        ];

        Restaurant::factory()->create(["id" => 1]);
        $this->assertCount(0, Restaurant::first()->dishes);

        $response = $this->postJson(
            "/api/v1/dishes",
            $data,
            $this->headers("create")
        );

        $response->assertStatus(201)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Jarret au Munster'
                 )
                 ->assertJsonPath(
                     'data.attributes.rating',
                     4.42
                 );

        $this->assertCount(1, Restaurant::first()->dishes);
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_can_patch_a_dish_for_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes(
                [
                    "id" => "1",
                    "name" => "Jarret au Camembert",
                    "rating" => 1.42,
                    "restaurant_id" => 1
                ]
            )->create(["id" => 1]);

        $data = [
            "data" => [
                "type" => "dishes",
                "id" => "1",
                "attributes" => [
                    "name" => "Jarret au Munster",
                    "rating" => 4.42,
                    "restaurant_id" => 1
                ],
            ]
        ];

        $response = $this->patchJson(
            "/api/v1/dishes/1",
            $data,
            $this->headers("patch")
        );

        $response->assertStatus(200)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Jarret au Munster'
                 )
                 ->assertJsonPath(
                     'data.attributes.rating',
                     4.42
                 );
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_delete_a_dish_from_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes(
                [
                    "id" => "1",
                    "name" => "Jarret au Camembert",
                    "rating" => 1.42,
                    "restaurant_id" => 1
                ]
            )->create(["id" => 1]);
        $data = [
            "data" => [
                "type" => "dishes",
                "id" => "1",
            ]
        ];

        $this->assertCount(1, Restaurant::first()->dishes);

        $response = $this->deleteJson(
            "/api/v1/dishes/1",
            $data,
            $this->headers("delete")
        );

        $this->assertCount(0, Restaurant::first()->dishes);
    }

    private function headers($method)
    {
        $user = User::factory()->create();
        $token = $user->createToken(
            "{$method}-dish",
            ["dish:{$method}"]
        )->plainTextToken;

        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$token}"
        ];
    }
}
