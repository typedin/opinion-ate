<?php

namespace Tests\Feature\Relationships;

use App\Models\Comment;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CanDecodeJson;
use Tests\Traits\HasHeader;

class CommentsTest extends TestCase
{
    use RefreshDatabase, HasHeader, CanDecodeJson;

    /**
     * @test
     */
    public function a_visitor_can_show_a_comment_with_its_relationships()
    {
        Comment::factory()
            ->has(User::factory())
            ->for(Dish::factory())
            ->create([
                "id" => 1,
                "body" => "Super good."
            ]);

        $response = $this->getJson(
            self::API_URL . "/comments/1",
            $this->headersWithNoCredentials()
        );

        $response->assertStatus(200);

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.id",
            "1"
        );

        $this->assertJsonValueEquals(
            $response->getContent(),
            "$.data.attributes.body",
            "Super good."
        );
        $this->assertArrayHasKey(
            "relationships",
            $this->decodedJson($response, "data")
        );

        $this->assertArrayHasKey(
            "user",
            $this->decodedJson($response, "data.relationships")
        );

        $this->assertArrayHasKey(
            "dish",
            $this->decodedJson($response, "data.relationships")
        );
    }
}
