<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function test_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'tipo_funcionario' => 'tipo 1',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
    /** @test */
    public function test_authenticate_Valid_Credential()
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $userData);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'token']);
    }

    /** @test */
    public function test_authenticate_Invalid_Credential()
    {
        $invalidUserData = [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ];

        $response = $this->postJson('/api/login', $invalidUserData);

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'Login credentials are invalid.']);
    }
}
