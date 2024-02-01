<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'tipo_funcionario' => 'suporte',
            'area_atuacao' => 'comercial',
            
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_register_with_invalid_data()
    {        
        $invalidUserData = [
            'name' => 'Test1 User',
            'email' => 'test1@example.com', // E-mail inválido
            'password' => 'pass', // Senha muito curta
            'tipo_funcionario' => 'suporte',
            'area_atuacao' => 'dp',
            
        ];
    
        $response = $this->postJson('/api/register', $invalidUserData);
    
        // Espera-se que a requisição falhe devido aos dados inválidos
        $response->assertStatus(400);
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
