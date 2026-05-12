<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'User Baru',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'member',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'access_token',
                     'token_type',
                     'user' => ['id', 'name', 'email', 'role'],
                 ]);
    }

    public function test_register_validation_error()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'User Baru',
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure(['errors']);
    }

    public function test_login_success()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'access_token',
                     'token_type',
                     'user' => ['id', 'name', 'email', 'role'],
                 ]);
    }

    public function test_login_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Email atau password salah']);
    }

    public function test_get_profile_authenticated()
    {
        $user = User::factory()->create([
            'role' => 'member',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'email', 'role']);
    }

    public function test_admin_can_list_users()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'total_count',
                     'limit',
                     'pagination' => ['next_page', 'current_page'],
                 ]);
    }

    public function test_member_cannot_list_users()
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);

        Sanctum::actingAs($member);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_change_password()
    {
        $member = User::factory()->create([
            'role' => 'member',
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($member);

        $response = $this->putJson('/api/v1/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password berhasil diubah']);
    }

    public function test_logout()
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logout berhasil']);
    }

    public function test_rate_limiting_throttling()
    {
        // Hit endpoint sebanyak batas limit (5 kali)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'randomuser@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Hit ke-6 harusnya ditolak karena melebihi batas (limit: 5 request/menit)
        $response = $this->postJson('/api/v1/login', [
            'email' => 'randomuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429); // 429 Too Many Requests
    }
}
