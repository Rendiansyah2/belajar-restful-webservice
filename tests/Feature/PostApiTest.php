<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;
    private Post $post;

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
    }

    // Test GET semua posts
    public function test_get_all_posts()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         ['id', 'title', 'status', 'content', 'user_id', 'created_at', 'updated_at'],
                     ],
                     'meta' => [
                         'total_count',
                         'limit',
                         'pagination' => ['next_page', 'current_page'],
                     ],
                 ]);
    }

    // Test GET semua posts (member)
    public function test_member_get_all_posts()
    {
        Sanctum::actingAs($this->member);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200);
    }

    // Test GET post by ID
    public function test_get_post_by_id()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/posts/'.$this->post->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'title', 'status', 'content', 'user_id', 'created_at', 'updated_at'],
                 ]);
    }

    // Test GET post by ID (member)
    public function test_member_get_post_by_id()
    {
        Sanctum::actingAs($this->member);

        $response = $this->getJson('/api/v1/posts/'.$this->post->id);

        $response->assertStatus(200);
    }

    // Test POST buat post baru
    public function test_create_post()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/posts', [
            'title'   => 'Test Post',
            'content' => 'Ini konten test',
            'status'  => 'draft',
            'user_id' => $this->admin->id
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => ['id', 'title', 'status', 'content', 'user_id', 'created_at', 'updated_at'],
                 ]);
    }

    // Test POST post baru (member - forbidden)
    public function test_member_cannot_create_post()
    {
        Sanctum::actingAs($this->member);

        $response = $this->postJson('/api/v1/posts', [
            'title'   => 'Test Post',
            'content' => 'Ini konten test',
            'status'  => 'draft',
            'user_id' => $this->member->id
        ]);

        $response->assertStatus(403);
    }

    // Test POST tanpa title (validation error)
    public function test_create_post_validation_error()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/posts', [
            'content' => 'Konten tanpa title',
            'status'  => 'draft',
            'user_id' => $this->admin->id
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure(['errors']);
    }

    // Test PUT update post
    public function test_update_post()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/posts/'.$this->post->id, [
            'title' => 'Judul Diupdate'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'title', 'status', 'content', 'user_id', 'created_at', 'updated_at'],
                 ]);
    }

    // Test PUT update post (member - forbidden)
    public function test_member_cannot_update_post()
    {
        Sanctum::actingAs($this->member);

        $response = $this->putJson('/api/v1/posts/'.$this->post->id, [
            'title' => 'Judul Diupdate by Member'
        ]);

        $response->assertStatus(403);
    }

    // Test DELETE post
    public function test_delete_post()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/v1/posts/'.$this->post->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'deleted successfully']);
    }

    // Test DELETE post (member - forbidden)
    public function test_member_cannot_delete_post()
    {
        Sanctum::actingAs($this->member);

        $response = $this->deleteJson('/api/v1/posts/'.$this->post->id);

        $response->assertStatus(403);
    }

    // Test akses tanpa token
    public function test_unauthorized_access()
    {
        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(401);
    }
}