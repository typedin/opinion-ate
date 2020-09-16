<?php

namespace Tests\Feature\Validation;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RestaurantsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function name_is_required()
    {
        //$this->withoutExceptionHandling();
        $data = [
            "data" => [
                "type" => "restaurants",
                "attributes" => [
                    "name" => "",
                    "address" => "some address here"
                ]
            ]
        ];

        $this->assertCount(0, Restaurant::all());

        $response = $this->postJson(
            "/api/v1/restaurants",
            $data,
            $this->headers("post")
        );
        
        dd($response);
        //$response->assertStatus(422);
        $this->assertCount(0, Restaurant::all());
    }

    private function headers($method)
    {
        $user = User::factory()->create();
        $token = $user->createToken(
            "{$method}-restaurant",
            ["restaurant:{$method}"]
        )->plainTextToken;

        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$token}"
        ];
    }
}
