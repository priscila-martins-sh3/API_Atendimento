<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Service;
use App\Models\Contact;
use App\Models\Support;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


class ServiceTest extends TestCase 
{
    use RefreshDatabase;

    
    public function test_store()
    {        
        $user = User::factory()->create(['tipo_funcionario' => 'admin']);  
        $token=auth()->login($user);      
        $contact = Contact::factory()->create();
                
        $serviceData = [
            'tipo_servico' => 'tirar_duvida',
            'retorno' => true,
            'informacoes' => null,
            'support_id' => null, 
            'contact_id' => $contact->id, 
        ];        

        $response = $this-> withHeader('Authorization', "Bearer $token")->postJson('/api/createservice', $serviceData);
        
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'tipo_servico',
                'retorno',
                'informacoes',
                'support_id',
                'contact_id',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJson([
            'success' => true,
            'message' => 'Serviço criado com sucesso',
            'data' => $serviceData,
        ]);
       
        $this->assertDatabaseHas('services', $serviceData);
    }

    public function test_update()
    {        
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user);              
        $service = Service::factory()->create();
                
        $updatedData = [
            'tipo_servico' => 'tirar_duvida',
            'retorno' => true,
            'informacoes' => 'Novas Informações adicionais',
            'support_id' => null, 
            'contact_id' => $service->contact_id,
        ];   
       
        $response = $this-> withHeader('Authorization', "Bearer $token")->putJson('/api/updateservice/' . $service->id, $updatedData);
       
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'tipo_servico',
                'retorno',
                'informacoes',
                'support_id',
                'contact_id',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJson([
            'success' => true,
            'message' => 'Serviço atualizado com sucesso',
            'data' => $updatedData,
        ]);
        
        $this->assertDatabaseHas('services', $updatedData);
    }
   
    public function test_index()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']);  
        $token=auth()->login($user);      
        $services = Service::factory()->count(3)->create();
       
        $response = $this-> withHeader('Authorization', "Bearer $token")->getJson('/api/services');      
       
        $response->assertStatus(200);     

        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'tipo_servico',
                    'retorno',
                    'informacoes',
                    'support_id',
                    'contact_id',
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
        $service = Service::factory()->create();
        
        $response = $this-> withHeader('Authorization', "Bearer $token")->getJson('/api/service/' . $service->id);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'tipo_servico',
                'retorno',
                'informacoes',
                'support_id',
                'contact_id',
                'created_at',
                'updated_at',              
                
            ],
        ]);

        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'tipo_servico' => $service->tipo_servico,
                'retorno'=> $service->retorno,
                'informacoes'=> $service->informacoes,
                'support_id'=> $service->support_id,
                'contact_id'=> $service->contact_id,
                'created_at' => $service->created_at->toISOString(),
                'updated_at' => $service->updated_at->toISOString(),
            ],
        ]);
    }
    public function test_destroy()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user);      
        $service = Service::factory()->create();
      
        $response = $this-> withHeader('Authorization', "Bearer $token")->deleteJson('/api/deleteservice/' . $service->id);

        $response->assertStatus(200);
        
        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }

    public function test_restore()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user);      
        $service = Service::factory()->create();
        $service->delete();
    
        $response = $this-> withHeader('Authorization', "Bearer $token")->postJson('/api/restoreservice/' . $service->id);

        $response->assertStatus(200);
        
        //$this->assertDatabaseHas('contacts', ['id' => $contact->id, 'deleted_at' => null]);

        $restoredTicket = Service::withTrashed()->find($service->id);
        $this->assertNotNull($restoredTicket);
        $this->assertNull($restoredTicket->deleted_at);
    }

    public function test_associate()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user); 
        $contact = Contact::factory()->create(['area_atendimento' => 'contabilidade']);               
        $support = Support::factory()->create(['area_atuacao' => 'contabilidade', 'livre' => true]);        
        $service = Service::factory()->create(['contact_id' => $contact->id, 'support_id' => $support->id]);

        $response = $this-> withHeader('Authorization', "Bearer $token")->putJson('/api/associate/' . $service->id);
               
        $response->assertStatus(200);        
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'tipo_servico',
                'retorno',
                'informacoes',
                'support_id',
                'contact_id',
                'created_at',
                'updated_at',
            ],
        ]);        

        $this->assertEquals($support->id, $service->fresh()->support_id);       
        $this->assertFalse($support->fresh()->livre);                 
   
        $response->assertJson([
            'success' => true,
            'message' => 'Serviço associado a um suporte com sucesso',
            'data' => [
                'id' => $service->id,
                'tipo_servico' => $service->tipo_servico,
                'retorno'=> false,
                'informacoes'=> $service->informacoes,
                'support_id'=> $support->id,
                'contact_id'=> $service->contact_id,
                'created_at' => $service->created_at->toISOString(),
                'updated_at' => $service->updated_at->toISOString(),               
            ],
        ]);
    }

    public function test_freeSupport()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'admin']); 
        $token=auth()->login($user); 
                     
        $support = Support::factory()->create();        
        $service = Service::factory()->create(['retorno' => false, 'support_id' => $support->id]);
                    
        $response = $this-> withHeader('Authorization', "Bearer $token")->putJson('/api/freesupport/' . $service->id);
    
        $response->assertStatus(200);    
        
        $this->assertTrue($support->fresh()->livre);    
        
        $response->assertJson([
            'success' => true,
            'message' => 'O status do suporte foi atualizado para livre.',
            'data' => [
                'id' => $support->id,
                'area_atuacao' => $support->area_atuacao,
                'livre'=> true,                
                'user_id'=> $support->user_id,                         
            ],
        ]);
    }

    public function test_findByServiceSupport()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'suporte']); 
        $token = auth()->login($user);                    
        $support = Support::factory()->create(['user_id' => $user->id]);        
    
        // Criar três serviços associados ao suporte
        $services = Service::factory()->count(3)->create([
            'retorno' => false,
            'support_id' => $support->id
        ]);
                 
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/services/support');
        //dd($response);
        $response->assertStatus(200);          
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'tipo_servico',
                    'retorno',
                    'informacoes',
                    'support_id',
                    'contact_id',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    
        // Verificar se todos os serviços retornados pertencem ao suporte correto
        foreach ($services as $service) {
            $response->assertJsonFragment(['support_id' => $support->id]);
        }    

        
    }

    /*public function test_FindByUnattendedServiceAreaSupport()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'suporte']); 
        $token = auth()->login($user);                   
        
        $contact = Contact::factory()->create(['area_atendimento' => 'contabilidade']);               
        $support = Support::factory()->create(['area_atuacao' => 'contabilidade', 'user_id' => $user->id]);        
        $service = Service::factory()->create(['contact_id' => $contact->id]);        

        $response = $this-> withHeader('Authorization', "Bearer $token")->getJson('/api/services/unattendedarea');

        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'tipo_servico',
                'retorno',
                'informacoes',
                'support_id',
                'contact_id',
                'created_at',
                'updated_at',              
            
            ],
        ]);

         $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'tipo_servico' => $service->tipo_servico,
                'retorno'=> $service->retorno,
                'informacoes'=> $service->informacoes,
                'support_id'=> $service->support_id,
                'contact_id'=> $service->contact_id,
                'created_at' => $service->created_at->toISOString(),
                'updated_at' => $service->updated_at->toISOString(),
            ],
        ]);    
    } */  
     
    public function test_FindByUnattendedServiceAreaSupport()
    {
        $user = User::factory()->create(['tipo_funcionario' => 'suporte']); 
        $token = auth()->login($user);                   
        
        $contact = Contact::factory()->create(['area_atendimento' => 'contabilidade']);               
        $support = Support::factory()->create(['area_atuacao' => 'contabilidade', 'user_id' => $user->id]);        
        $service = Service::factory()->create(['contact_id' => $contact->id]);        

        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/services/unattendedarea');

        $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'tipo_servico',
                            'retorno',
                            'informacoes',
                            'support_id',
                            'contact_id',
                            'created_at',
                            'updated_at',              
                        ],
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        [
                            'id' => $service->id,
                            'tipo_servico' => $service->tipo_servico,
                            'retorno' => $service->retorno,
                            'informacoes' => $service->informacoes,
                            'support_id' => $service->support_id,
                            'contact_id' => $service->contact_id,
                            'created_at' => $service->created_at->toISOString(),
                            'updated_at' => $service->updated_at->toISOString(),
                        ],
                    ],
                ], true);
    }

    

}
