<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_currentUser_whenUnauthorized()
    {
        $response = $this->json('get', 'api/me');
        $response->assertStatus(401);
    }

    public function test_currentUser()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->json('get','api/me');

        $response->assertOk();
        $response->assertJsonFragment($user->toArray());
    }

    public function test_bootstrap_whenUnauthorized()
    {
        $response = $this->json('get', 'api/bootstrap');
        $response->assertStatus(401);
    }

    public function test_bootstrap()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->json('get', 'api/bootstrap');

        $response->assertOk();
        $response->assertJsonFragment(['user' => $user->toArray()]);
    }
}
