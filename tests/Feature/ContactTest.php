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
        $token=auth()->login($user);           
        $contactData = [
            'nome_pessoa' => 'Teste Pessoa',
            'nome_cliente' => 'Teste Cliente',
            'area_atendimento' => 'comercial',            
        ];        

        $response = $this-> withHeader('Authorization', "Bearer $token")->postJson('/api/createcontact', $contactData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'nome_pessoa' => $contactData['nome_pessoa'],
                'nome_cliente' => $contactData['nome_cliente'],
                'area_atendimento' => $contactData['area_atendimento'],
            ],
        ]);
        $this->assertDatabaseHas('contacts', $contactData);
   
    }
    
    public function test_update()
    {        
        $user = User::factory()->create(['tipo_funcionario' => 'admin']);  
        $token=auth()->login($user);
        $contact = Contact::factory()->create();
                
        $updatedData = [
            'nome_pessoa' => 'Novo Nome',
            'nome_cliente' => 'Novo Cliente',
            'area_atendimento' => 'Nova Área',
        ];    
       
        $response = $this-> withHeader('Authorization', "Bearer $token")->putJson('/api/updatecontact/' . $contact->id, $updatedData);
       
        $response->assertStatus(200);        
        $response->assertJson([
            'success' => true,
            'data' => [
                
                'nome_pessoa' => $updatedData['nome_pessoa'],
                'nome_cliente' => $updatedData['nome_cliente'],
                'area_atendimento' => $updatedData['area_atendimento'],
            ],
        ]);

        $this->assertDatabaseHas('contacts', $updatedData);
    }  
   
    public function test_index()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']);  
        $token=auth()->login($user);      
        $contacts = Contact::factory()->count(3)->create();
       
        $response = $this-> withHeader('Authorization', "Bearer $token")->getJson('/api/contacts');      
       
        $response->assertStatus(200);     

        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'nome_pessoa',
                    'nome_cliente',
                    'area_atendimento',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);       
    }
       
    public function test_show()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user);      
        $contact = Contact::factory()->create();
        
        $response = $this-> withHeader('Authorization', "Bearer $token")->getJson('/api/contact/' . $contact->id);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'nome_pessoa',
                'nome_cliente',
                'area_atendimento',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $contact->id,
                'nome_pessoa' => $contact->nome_pessoa,
                'nome_cliente' => $contact->nome_cliente,
                'area_atendimento' => $contact->area_atendimento,
                'created_at' => $contact->created_at->toISOString(),
                'updated_at' => $contact->updated_at->toISOString(),
            ],
        ]);
    }
    public function test_destroy()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user);      
        $contact = Contact::factory()->create();
      
        $response = $this-> withHeader('Authorization', "Bearer $token")->deleteJson('/api/deletecontact/' . $contact->id);

        $response->assertStatus(200);
        
        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_restore()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user);      
        $contact = Contact::factory()->create();
        $contact->delete();
    
        $response = $this-> withHeader('Authorization', "Bearer $token")->postJson('/api/restorecontact/' . $contact->id);

        $response->assertStatus(200);
        
        //$this->assertDatabaseHas('contacts', ['id' => $contact->id, 'deleted_at' => null]);

        $restoredTicket = Contact::withTrashed()->find($contact->id);
        $this->assertNotNull($restoredTicket);
        $this->assertNull($restoredTicket->deleted_at);
    }


}
//$obj->toArray()

