<?php

namespace Tests\Feature\Validation;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class DishesTest extends TestCase
{
    const MODEL = "dish";

    use RefreshDatabase, HasHeader;

    /**
     * @dataProvider validationProvider
     * @test
     */
    public function validationTest($data, $validation)
    {
        $restaurant1 = Restaurant::factory()
            ->has(Dish::factory()->state([
                "user_id" => "1",
                "id" => 1,
                "name" => "Jarret au Munster",
            ]))
            ->create([
                "id" => 1
            ]);
        $restaurant2 = Restaurant::factory()
            ->has(Dish::factory()->state([
                "user_id" => "1",
                "id" => 42,
                "name" => "Jarret au Munster",
            ]))
            ->create([
                "id" => 42,
                "name" => "Another Restaurant with the same dish name"
            ]);
        $this->assertCount(2, Dish::all());
        $this->assertCount(1, $restaurant1->dishes);

        $response = $this->postJson(
            "/api/v1/dishes",
            $data,
            $this->headersWithCredentials("post", "1")
        );


        foreach ($validation as $key => $value) {
            $this->assertEquals(
                $value,
                $response->json("errors")[0][$key]
            );
        }

        $this->assertCount(2, Dish::all());
        $this->assertCount(1, $restaurant1->dishes);
    }


    /**
     * @test
     */
    public function same_dish_name_for_different_restaurants_can_be_created()
    {
        $restaurant1 = Restaurant::factory()
            ->has(Dish::factory()->state([
                "user_id" => "1",
                "id" => 1,
                "name" => "Jarret au Munster",
            ]))
            ->create([
                "id" => 1
            ]);

        $restaurant2 = Restaurant::factory()->create([ "id" => 2 ]);

        $this->assertCount(1, Dish::all());

        $response = $this->postJson(
            "/api/v1/dishes",
            [
                "data" => [
                    "type" => "dishes",
                    "attributes" => [
                        "name" =>  "Jarret au Munster",
                        "user_id" => "1",
                        "restaurant_id" => "2"
                    ],
                ]
            ],
            $this->headersWithCredentials("post", "1")
        );

        $response->assertStatus(201);

        $this->assertCount(2, Dish::all());
    }

    public function validationProvider()
    {
        return [
            [
                $this->nameData([
                    "name" => null
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name field is required."
                ]
            ],
            [
                $this->nameData([
                    "name" => 42
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name must be a string."
                ]
            ],
            [
                $this->nameData([
                    "name" => "Jarret au Munster"
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name has already been taken."
                ]
            ],
            [
                $this->nameData([
                    "restaurant_id" => null,
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The restaurant id field is required."
                ]
            ],
            [
                $this->nameData([
                    "user_id" => null,
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The user id field is required."
                ]
            ],
        ];
    }

    private function nameData(array $overrides): array
    {
        return [
            "data" => [
                "type" => "dishes",
                "attributes" => array_merge([
                    "name" => "A name that has not been taken.",
                    "user_id" => "1",
                    "restaurant_id" => "1",
                ], $overrides),
            ]
        ];
    }
}
