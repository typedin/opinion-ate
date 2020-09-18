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
                "id" => 1,
                "name" => "Jarret au Munster",
            ]))
            ->create([
                "id" => 1
            ]);
        $restaurant2 = Restaurant::factory()
            ->has(Dish::factory()->state([
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
            $this->headersWithCredentials("post")
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
                        "restaurant_id" => 2
                    ],
                ]
            ],
            $this->headersWithCredentials("post")
        );

        $response->assertStatus(201);

        $this->assertCount(2, Dish::all());
    }

    public function validationProvider()
    {
        return [
            [
                $this->nameData(null),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name field is required."
                ]
            ],
            [
                $this->nameData(42),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name must be a string."
                ]
            ],
            [
                $this->nameData("Jarret au Munster"),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name has already been taken."
                ]
            ],
            [
                [
                    "data" => [
                        "type" => "dishes",
                        "attributes" => [
                            "name" => "A Name for a dish with restaurant id set to null",
                            "restaurant_id" => null,
                        ],
                    ],
                ],
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The restaurant id field is required."
                ]
            ],
            [
                [
                    "data" => [
                        "type" => "dishes",
                        "attributes" => [
                            "name" => "A Name for a dish with no restaurant id key value pair",
                        ],
                    ],
                ],
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The restaurant id field is required."
                ]
            ],
        ];
    }

    private function nameData($value)
    {
        return [
            "data" => [
                "type" => "dishes",
                "attributes" => [
                    "name" => $value,
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
    }
}
