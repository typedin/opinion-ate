<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Dish;
use Helmich\JsonAssert\JsonAssertions;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\Traits\HasHeader;

class CommentsTest extends TestCase
{
    use RefreshDatabase, HasHeader, JsonAssertions;

    const MODEL = "comment";

    /**
     * @test
     */
    public function anyone_can_index_all_the_comments()
    {
        Comment::factory()->count(4)->create();

        $response = $this->getJson(
            "api/v1/comments",
            $this->emptyHeader()
        );
    
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function anyone_can_index_all_the_comments_for_a_dish()
    {
        Dish::factory()
            ->has(Comment::factory()->count(4)
            ->state(new Sequence(
                ["body" => "So goooood."],
                ["body" => "Very good."],
            )))
            ->create(["id" => 1]);

        $response = $this->getJson(
            "api/v1/dishes/1/comments",
            $this->headersWithNoCredentials()
        );
    
        $response->assertStatus(200);

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[0].attributes.body",
            "So goooood."
        );

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data[1].attributes.body",
            "Very good."
        );
    }

    /**
     * @test
     */
    public function a_guest_cannot_create_a_comment_for_a_dish()
    {
        $dish = Dish::factory()->create(["id" => 1]);
        
        $this->assertCount(0, $dish->comments);

        $response = $this->postJson(
            "api/v1/comments",
            $this->dataWithMergedAttributes([
                    "body" => "Delicious.",
                    "dish_id" => 1,
                    "user_id" => 1,
            ]),
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);
        $this->assertCount(0, $dish->fresh()->comments);
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_create_a_comment_for_a_dish()
    {
        $dish = Dish::factory()->create(["id" => 1]);
        
        $this->assertCount(0, $dish->comments);

        $response = $this->postJson(
            "api/v1/comments",
            [
                "data" => [
                    "type" => "comments",
                    "attributes" => [
                        "body" => "Delicious.",
                        "dish_id" => "1",
                        "user_id" => "1",
                    ],
                ]
            ],
            $this->headersWithCredentials("create")
        );

        $response->assertStatus(201);
        $this->assertCount(1, $dish->fresh()->comments);
        $this->assertEquals("Delicious.", $dish->fresh()->comments->first()->body);
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_update_a_comment_for_a_dish()
    {
        $dish = Dish::factory()
            ->has(Comment::factory()->state([
                "id" => 1,
                "body" => "Good.",
                "user_id" => 1
            ]))
            ->create(["id" => 1]);
        
        $this->assertCount(1, $dish->comments);
        $this->assertEquals("Good.", $dish->fresh()->comments->first()->body);

        $response = $this->patchJson(
            "api/v1/comments/1",
            $this->dataWithMergedAttributes([
                    "body" => "Delicious.",
            ]),
            $this->headersWithCredentials("update")
        );

        $response->assertStatus(200);
        $this->assertCount(1, $dish->fresh()->comments);
        $this->assertEquals("Delicious.", $dish->fresh()->comments->first()->body);
    }

    /**
     * @test
     */
    public function a_guest_cannot_update_a_comment_for_a_dish()
    {
        $dish = Dish::factory()
            ->has(Comment::factory()->state([
                "id" => 1,
                "body" => "Good.",
                "user_id" => 1
            ]))
            ->create(["id" => 1]);
        
        $this->assertCount(1, $dish->comments);
        $this->assertEquals("Good.", $dish->fresh()->comments->first()->body);

        $response = $this->patchJson(
            "api/v1/comments/1",
            $this->dataWithMergedAttributes([
                    "body" => "Delicious.",
            ]),
            $this->headersWithNoCredentials("update")
        );

        $response->assertStatus(401);
        $this->assertCount(1, $dish->comments);
        $this->assertEquals("Good.", $dish->fresh()->comments->first()->body);
    }

    /**
     * @test
     */
    public function a_user_with_a_token_can_delete_a_comment_for_a_dish()
    {
        $dish = Dish::factory()
            ->has(Comment::factory()->state([
                "id" => 1,
                "user_id" => 1,
            ]))
            ->create(["id" => 1]);
        
        $this->assertCount(1, $dish->comments);

        $response = $this->deleteJson(
            "api/v1/comments/1",
            $this->dataWithMergedAttributes([]),
            $this->headersWithCredentials("delete")
        );

        $response->assertStatus(204);
        $this->assertCount(0, $dish->fresh()->comments);
    }

    /**
     * @test
     */
    public function a_guest_cannot_delete_a_comment_for_a_dish()
    {
        $dish = Dish::factory()
            ->has(Comment::factory()->state([
                "id" => 1,
                "user_id" => 1,
            ]))
            ->create(["id" => 1]);
        
        $this->assertCount(1, $dish->comments);

        $response = $this->deleteJson(
            "api/v1/comments/1",
            $this->dataWithMergedAttributes([]),
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(401);
        $this->assertCount(1, $dish->fresh()->comments);
    }

    private function dataWithMergedAttributes(array $overrides=[]): array
    {
        return [
            "data" => [
                "type" => "comments",
                "id" => "1",
                "attributes" => array_merge([
                    "body" => "Great Dish",
                    "dish_id" => "1",
                    "user_id" => "1"
                ], $overrides)
            ]
        ];
    }
}
