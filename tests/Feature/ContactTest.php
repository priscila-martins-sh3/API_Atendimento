<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Contact;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class ContactTest extends TestCase
{
    //use RefreshDatabase;

    
    public function test_store()
    {        
        $user = User::factory()->create(['tipo_funcionario' => 'admin']);  
        $token= auth()->login($user);         
        //dd($token); 
        $contactData = [
            'nome_pessoa' => 'Teste Pessoa',
            'nome_cliente' => 'Teste Cliente',
            'area_atendimento' => 'DP',            
        ];
        //dd($contactData); 
        // Simula uma requisição HTTP POST para o endpoint de criação de contatos
        $response = $this-> withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/createcontact', $contactData);
        dd($response); 
       

        $response->assertStatus(500)
        ->assertJsonFragment($contactData);

        $this->assertDatabaseHas('contacts', $contactData);
    }
       
    
}
//$obj->toArray()