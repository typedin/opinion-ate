<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Rating;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\HasHeader;
use Helmich\JsonAssert\JsonAssertions;

class DishesTest extends TestCase
{
    const MODEL = "dish";

    use RefreshDatabase, HasHeader, JsonAssertions;


    /**
     * @test
     */
    public function a_non_registered_user_can_index_all_dishes()
    {
        Dish::factory()
            ->for(Restaurant::factory())
            ->has(Rating::factory()->count(1)->state(["value" => 4.42]))
            ->count(3)
            ->state(new Sequence(
                [ "name" => "AppleSauce" ],
                [ "name" => "Chesseburger" ],
                [ "name" => "Jarret au Munster" ],
            ))
            ->create();

        $response = $this->getJson(
            "api/v1/dishes",
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(200);

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[0].attributes.name",
            "Chesseburger"
        );
        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[0].attributes.rating",
            4.42
        );
        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[1].attributes.name",
            "AppleSauce"
        );
        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[1].attributes.rating",
            4.42
        );
        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[2].attributes.name",
            "Jarret au Munster"
        );
        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[2].attributes.rating",
            4.42
        );
    }

    /**
     * @test
     */
    public function a_non_registered_user_can_read_a_dish()
    {
        Dish::factory()
            ->for(Restaurant::factory())
            ->has(Rating::factory()->count(1)->state(["value" => 4.42]))
            ->create([
                "id" => 1,
                "name" => "Jarret au Munster",
            ]);

        $response = $this->getJson(
            "api/v1/dishes/1",
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(200)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Jarret au Munster',
                 )->assertJsonPath(
                     "data.attributes.rating",
                     "4.42"
                 );
    }


    /**
     * @test
     */
    public function a_guest_cannot_create_a_dish_for_a_restaurant()
    {
        Restaurant::factory()->create(["id" => 1]);
        $this->assertCount(0, Restaurant::first()->dishes);

        $response = $this->postJson(
            "/api/v1/dishes",
            $this->dataWithMergedAttributes([
                    "name" => "Jarret au Munster",
                    "restaurant_id" => 1
            ]),
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);
        $this->assertCount(0, Restaurant::first()->dishes);
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_can_create_a_dish_for_a_restaurant()
    {
        $dataWithNoId = [
            "data" => [
                "type" => "dishes",
                "attributes" => [
                    "name" => "Jarret au Munster",
                    "user_id" => "1",
                    "restaurant_id" => "1"
                ],
            ]
        ];

        Restaurant::factory()->create(["id" => 1]);
        $this->assertCount(0, Restaurant::first()->dishes);

        $response = $this->postJson(
            "/api/v1/dishes",
            $dataWithNoId,
            $this->headersWithCredentials("create")
        );

        $response->assertStatus(201)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Jarret au Munster'
                 );

        $this->assertCount(1, Restaurant::first()->dishes);
    }


    /**
     * @test
     */
    public function a_guest_cannot_patch_a_dish_for_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes(
                [
                    "id" => "1",
                    "name" => "Jarret au Camembert",
                    "restaurant_id" => 1
                ]
            )->create(["id" => 1]);


        $response = $this->patchJson(
            "/api/v1/dishes/1",
            $this->dataWithMergedAttributes([
                "name" => "Jarret au Munster",
                "restaurant_id" => 1
            ]),
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);

        $this->assertEquals("Jarret au Camembert", Dish::first()->name);
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_can_patch_a_dish_for_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes([
                    "id" => "1",
                    "user_id" => "1",
                    "name" => "Jarret au Camembert",
                    "restaurant_id" => "1"
            ])
            ->create(["id" => 1]);

        $response = $this->patchJson(
            "/api/v1/dishes/1",
            $this->dataWithMergedAttributes([
                    "name" => "Jarret au Munster",
                    "user_id" => 1,
                    "restaurant_id" => 1
            ]),
            $this->headersWithCredentials("patch")
        );

        $response->assertStatus(200)
                 ->assertJsonPath(
                     'data.attributes.name',
                     'Jarret au Munster'
                 );
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_cannot_patch_someonelses_dish_for_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes([
                    "id" => "1",
                    "name" => "Jarret au Camembert",
                    "restaurant_id" => 1
            ])
            ->create(["id" => 1]);

        $response = $this->patchJson(
            "/api/v1/dishes/1",
            $this->dataWithMergedAttributes([
                    "name" => "Jarret au Munster",
                    "user_id" => 42,
                    "restaurant_id" => 1
            ]),
            $this->headersWithCredentials("patch", 42)
        );

        $response->assertStatus(401);
    }


    /**
     * @test
     */
    public function a_guest_cannot_delete_a_dish_from_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes(
                [
                    "id" => "1",
                    "name" => "Jarret au Camembert",
                    "restaurant_id" => 1
                ]
            )->create(["id" => 1]);

        $this->assertCount(1, Restaurant::first()->dishes);

        $response = $this->deleteJson(
            "/api/v1/dishes/1",
            $this->dataWithMergedAttributes(),
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);

        $this->assertCount(1, Restaurant::first()->dishes);
    }

    /**
     * @test
     */
    public function an_owner_of_a_dish_with_a_token_can_delete_it_from_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes([
                "id" => 1,
                "restaurant_id" => 1,
                "user_id" => 1,
                "name" => "Jarret au Camembert",
            ])->create([
                "id" => 1,
            ]);

        $this->assertCount(1, Restaurant::first()->dishes);

        $response = $this->deleteJson(
            "/api/v1/dishes/1",
            $this->dataWithMergedAttributes([
                "user_id" => 1,
                "restaurant_id" => 1,
            ]),
            $this->headersWithCredentials("delete", 1)
        );
    
        $this->assertCount(0, Restaurant::first()->dishes);
    }

    /**
     * @test
     */
    public function a_user_with_a_valid_token_cannot_delete_someonelses_dish_for_a_restaurant()
    {
        Restaurant::factory()
            ->hasDishes([
                    "id" => 1,
                    "name" => "Jarret au Camembert",
                    "restaurant_id" => 1
            ])
            ->create(["id" => 1]);

        $response = $this->deleteJson(
            "/api/v1/dishes/1",
            $this->dataWithMergedAttributes(),
            $this->headersWithCredentials("delete", 42)
        );

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function a_guest_cannot_assign_an_existing_dish_to_another_restaurant()
    {
        $restaurant1 = Restaurant::factory()
            ->hasDishes(
                [
                    "id" => 1,
                    "name" => "Jarret au Camembert",
                    "restaurant_id" => 1
                ]
            )->create(["id" => 1]);

        $restaurant2 = Restaurant::factory()
            ->create(["id" => 2]);

        $this->assertCount(1, $restaurant1->dishes);
        $this->assertCount(0, $restaurant2->dishes);

        $response = $this->deleteJson(
            "/api/v1/dishes/1",
            [
                "data" => [
                    "type" => "dishes",
                    "id" => "1",
                    "attributes" => [
                        "restaurant_id" => 2
                    ],
                ]
            ],
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);

        $this->assertCount(1, $restaurant1->fresh()->dishes);
        $this->assertCount(0, $restaurant2->fresh()->dishes);
    }

    private function dataWithMergedAttributes(array $attributes=[]): array
    {
        return [
            "data" => [
                "type" => "dishes",
                "id" => "1",
                "attributes" => $attributes,
            ]
        ];
    }
}
