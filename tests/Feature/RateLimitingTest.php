<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rate_limiting_returns_429()
    {
        // Dalam api.php, kita menggunakan middleware throttle:5,1 
        // Artinya maksimal 5 request per menit.
        
        $url = '/api/v1/register'; // Menggunakan endpoint public agar tidak terhalang Auth middleware

        // Lakukan 5 request pertama (batas maksimal)
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson($url, [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => 'password123',
                'role' => 'member',
            ]);
        }

        // Request ke-6 harusnya diblokir oleh throttle dan mengembalikan status 429 Too Many Requests
        $response = $this->postJson($url, [
            'name' => "User 6",
            'email' => "user6@example.com",
            'password' => 'password123',
            'role' => 'member',
        ]);
        
        $response->assertStatus(429);
    }
}
