<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    //use RefreshDatabase;

    
    public function test_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'tipo_funcionario' => 'admin',
            'area_atuacao' => '',
            
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
            'email' => 'test1@example.com', 
            'password' => 'pass', // Senha muito curta
            'tipo_funcionario' => 'suporte',
            'area_atuacao' => '',
            
        ];
    
        $response = $this->postJson('/api/register', $invalidUserData);
    
        // Espera-se que a requisição falhe devido aos dados inválidos
        $response->assertStatus(400);
    }
    
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

    public function test_authenticate_Invalid_Credential()
    {
        $invalidUserData = [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ];

        $response = $this->postJson('/api/login', $invalidUserData);

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'As credenciais de login são invalidas.']);
    }


    public function test_logout_Valid_Credential()
    {
    $user = User::factory()->create(['tipo_funcionario' => 'admin']);
    $token= auth()->login($user);
    
    //$authResponse = $this->postJson('/api/login', $userData);

    //$authResponse->assertStatus(200)
     //            ->assertJsonStructure(['success', 'token']);

    // Em seguida, fazemos o logout com o token obtido na autenticação
   // $token = $resposta->json('token');   
       
    $logoutResponse = $this-> withHeader('Authorization', "Bearer $token")->postJson('/api/logout', ['token' => $token]); 
    $logoutResponse->assertStatus(200)
                   ->assertJsonFragment([
                       'success' => true,
                       'message' => 'O usuário foi desconectado.'
                   ]);
    }


}
