<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use RefreshDatabase;

    
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

        $response->assertStatus(200)
                 ->assertJsonStructure(['success','data']);

        /*$this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);*/
    }

    public function test_register_with_invalid_data()
    {        
        $invalidUserData = [
            'name' => 'Test User',
            'email' => 'test@example.com', 
            'password' => 'pass', // Senha muito curta
            'tipo_funcionario' => 'admin',
            'area_atuacao' => '',
            
        ];
    
        $response = $this->postJson('/api/register', $invalidUserData);    
        
        $response->assertStatus(400);
    }
    
    public function test_authenticate_Valid_Credential()
    {
        User::factory()->create([   
            'email' => 'test@example.com',
            'password' => 'password',
            'tipo_funcionario' => 'admin',
        ]);
        
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

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
                 ->assertJsonStructure(['success','message']);
                 //->assertJson(['success' => false, 'message' => 'As credenciais de login são invalidas.']);
    }


    public function test_logout_Valid_Credential()
    {
    $user= User::factory()->create(['tipo_funcionario' => 'admin']);   
    $token= auth()->login($user);       
       
    $response = $this-> withHeader('Authorization', "Bearer $token")->postJson('/api/logout', ['token' => $token]); 
    
    $response->assertStatus(200)            
                   ->assertJsonFragment([
                       'success' => true,
                       'message' => 'O usuário foi desconectado.'
                   ]);
    }


}
