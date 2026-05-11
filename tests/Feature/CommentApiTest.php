<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;
    private Post $post;
    private Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->member = User::factory()->create([
            'role' => 'member',
        ]);

        $this->post = Post::create([
            'title' => 'Seed Post',
            'content' => 'Seed content',
            'status' => 'draft',
            'user_id' => $this->admin->id,
        ]);

        $this->comment = Comment::create([
            'comment' => 'Seed comment',
            'post_id' => $this->post->id,
            'user_id' => $this->member->id,
        ]);
    }

    public function test_admin_can_get_comments()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/posts/'.$this->post->id.'/comments');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         ['id', 'comment', 'post_id', 'user_id', 'created_at', 'updated_at'],
                     ],
                     'meta' => [
                         'total_count',
                         'limit',
                         'pagination' => ['next_page', 'current_page'],
                     ],
                 ]);
    }

    public function test_member_can_get_comments()
    {
        Sanctum::actingAs($this->member);
        $response = $this->getJson('/api/v1/posts/'.$this->post->id.'/comments');
        $response->assertStatus(200);
    }

    public function test_admin_can_get_comment_by_id()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/v1/comments/'.$this->comment->id);
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'comment', 'post_id', 'user_id', 'created_at', 'updated_at']
                 ]);
    }

    public function test_member_can_get_comment_by_id()
    {
        Sanctum::actingAs($this->member);
        $response = $this->getJson('/api/v1/comments/'.$this->comment->id);
        $response->assertStatus(200);
    }

    public function test_admin_can_create_comment()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/posts/'.$this->post->id.'/comments', [
            'comment' => 'Komentar admin',
            'user_id' => $this->admin->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => ['id', 'comment', 'post_id', 'user_id', 'created_at', 'updated_at'],
                 ]);
    }

    public function test_member_cannot_create_comment()
    {
        Sanctum::actingAs($this->member);

        $response = $this->postJson('/api/v1/posts/'.$this->post->id.'/comments', [
            'comment' => 'Komentar member',
            'user_id' => $this->member->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_comment()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/comments/'.$this->comment->id, [
            'comment' => 'Komentar diupdate admin',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'comment', 'post_id', 'user_id', 'created_at', 'updated_at'],
                 ]);
    }

    public function test_member_cannot_update_comment()
    {
        Sanctum::actingAs($this->member);

        $response = $this->putJson('/api/v1/comments/'.$this->comment->id, [
            'comment' => 'Komentar diupdate member',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_comment()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/v1/comments/'.$this->comment->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'deleted successfully']);
    }

    public function test_member_cannot_delete_comment()
    {
        Sanctum::actingAs($this->member);

        $response = $this->deleteJson('/api/v1/comments/'.$this->comment->id);

        $response->assertStatus(403);
    }

    public function test_unauthorized_comment_access()
    {
        $response = $this->getJson('/api/v1/posts/'.$this->post->id.'/comments');

        $response->assertStatus(401);
    }
}
