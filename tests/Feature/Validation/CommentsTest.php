<?php

namespace Tests\Feature\Validation;

use App\Models\Comment;
use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class CommentsTest extends TestCase
{
    const MODEL = "comment";

    use RefreshDatabase, HasHeader;

    /**
     * @dataProvider validationProvider
     * @test
     */
    public function validationTest($data, $validation)
    {
        $dish1 = Dish::factory()
            ->has(Comment::factory()->state([
                "id" => 1,
                "user_id" => 1,
                "body" => "Great Jarret au Munster",
            ]))
            ->create([
                "user_id" => 1,
                "id" => 1
            ]);
        $dish2 = Dish::factory()
            ->has(Comment::factory()->state([
                "id" => 42,
                "user_id" => 1,
                "body" => "Another comment for a Jarret au Munster in another restaurant",
            ]))
            ->create([
                "user_id" => 1,
                "id" => 42,
            ]);

        $this->assertCount(2, Comment::all());
        $this->assertCount(1, $dish1->comments);

        $response = $this->postJson(
            "/api/v1/comments",
            $data,
            $this->headersWithCredentials("post", 1)
        );


        foreach ($validation as $key => $value) {
            $this->assertEquals(
                $value,
                $response->json("errors")[0][$key]
            );
        }

        $this->assertCount(2, Comment::all());
        $this->assertCount(1, $dish1->comments);
    }

    public function validationProvider()
    {
        return [
            [
                $this->bodyData([
                    "body" => null
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The body field is required."
                ]
            ],
            [
                $this->bodyData([
                    "body" => 42
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The body must be a string."
                ]
            ],
            [
                $this->bodyData([
                    "dish_id" => null
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The dish id field is required."
                ]
            ],
            [
                $this->bodyData([
                    "user_id" => null
                ]),
                "validation" => [
                    "status" => "422",
                    "title" => "Unprocessable Entity",
                    "detail" => "The user id field is required."
                ]
            ],
        ];
    }

    private function bodyData(array $overrides): array
    {
        return [
            "data" => [
                "type" => "comments",
                "attributes" => array_merge([
                    "body" => "Great Dish",
                    "dish_id" => 1,
                    "user_id" => 1
                ], $overrides)
            ]
        ];
    }
}
