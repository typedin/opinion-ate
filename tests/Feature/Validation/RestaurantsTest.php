<?php

namespace Tests\Feature\Validation;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class RestaurantsTest extends TestCase
{
    const MODEL = "restaurant";

    use RefreshDatabase, HasHeader;

    /**
     * @dataProvider validationProvider
     */
    public function testUniqueness($data, $validation)
    {
        Restaurant::factory()->create([
            "name" => "Already Taken Name",
            "address" => "Already Taken Address"
        ]);

        $this->assertCount(1, Restaurant::all());

        $response = $this->postJson(
            "/api/v1/restaurants",
            $data,
            $this->headersWithCredentials("post")
        );

        foreach ($validation as $key => $value) {
            $this->assertEquals(
                $value,
                $response->json("errors")[0][$key]
            );
        }

        $this->assertCount(1, Restaurant::all());
    }

    public function validationProvider(): array
    {
        return [
            [
                $this->nameData(42),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name must be a string."
                ]
            ],
            [
                $this->nameData(null),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name field is required."
                ]
            ],
            [
                $this->addressData(42),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The address must be a string."
                ]
            ],
            [
                $this->addressData(null),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The address field is required."
                ]
            ],
            [
                $this->nameData("Already Taken Name"),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The name has already been taken."
                ]
            ],
            [
                $this->addressData("Already Taken Address"),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The address has already been taken."
                ]
            ]
        ];
    }

    private function nameData($value)
    {
        return [
            "data" => [
                "type" => "restaurants",
                "attributes" => [
                    "name" => $value,
                    "address" => "some address here"
                ]
            ]
        ];
    }

    private function addressData($value)
    {
        return [
            "data" => [
                "type" => "restaurants",
                "attributes" => [
                    "name" => "A Restaurant Name",
                    "address" => $value
                ]
            ]
        ];
    }
}
