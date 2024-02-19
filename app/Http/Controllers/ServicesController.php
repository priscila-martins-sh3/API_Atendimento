<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Support;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{      

    /**
     * Store a newly created resource in storage.   
     */
    public function store(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            
            'tipo_servico'=>'required|string|in:' . Service::tiposValidosServico(),            
            'retorno' => 'required|boolean', 
            'informacoes' => 'nullable',    
            'support_id' => 'nullable',  
            'contact_id' => 'required',    
            
        ]); 

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }      
    
        $service = Service::create([      	
        	    
            'tipo_servico'=> $request->tipo_servico,        
            'retorno' => $request->retorno,
            'informacoes' => $request->informacoes,
            'support_id' => $request->support_id,
            'contact_id' => $request->contact_id,
        ]);            
        
        /*if ($request->filled('support_id')) {
            Support::where('id', $request->support_id)->update(['livre' => false]);
        } */   
        return response()->json([
            'success' => true,
            'message' => 'Serviço criado com sucesso',
            'data' => $service
            ], Response::HTTP_OK);
    }           

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::all();
        return response()->json(['success' => true, 'data' => $services], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show ($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['error' => 'Serviço não encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $service], Response::HTTP_OK);
    }

    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)    
    {        
        $validator = Validator::make($request->all(), [
            
            'tipo_servico'=>'required|string|in:' . Service::tiposValidosServico(),            
            'retorno' => 'required|boolean', 
            'informacoes' => 'nullable',    
            'support_id' => 'nullable',  
            'contact_id' => 'required',    
           
        ]);

        // Verificar se a validação falhou
        if ($validator->fails()) {
        return response()->json(['error' => $validator->messages()], 400);
        }

        // Atualizar os atributos do serviço
        $service->update($request->all());

        /*if ($request->filled('support_id')) {
            Support::where('id', $request->support_id)->update(['livre' => false]);
        }*/
        // Responder com sucesso
        return response()->json([
            'success' => true,
            'message' => 'Serviço atualizado com sucesso',
            'data' => $service
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Serviço deletado com sucesso'
        ], Response::HTTP_OK);
    }

    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id); 
        $service->restore(); 

        return response()->json([
            'success' => true,
            'message' => 'Serviço restaurado com sucesso'
    ], Response::HTTP_OK);        
    }

    public function associate(Service $service)
    {
        // Encontrar o contato associado ao serviço
        $contact = $service->contact;
        
        // Encontrar o suporte disponível na mesma área de atuação do contato
        $support = Support::where('area_atuacao', $contact->area_atendimento)
                          ->where('livre', true)
                          ->first();
    
        if (!$support) {
            return response()->json(['error' => 'Nenhum suporte disponível na mesma área de atuação'], 404);
        }
    
        // Associar o serviço ao suporte encontrado
        $service->update(['support_id' => $support->id,
                          'retorno' => false]);
    
        // Atualizar o status de disponibilidade do suporte        
        $support->livre = false;
        $support->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Serviço associado a um suporte com sucesso',
            'data' => $service
        ], Response::HTTP_OK);
    }

    public function freeSupport($serviceId)
    {
        // Encontrar o serviço pelo ID
        $service = Service::find($serviceId);
    
        // Verificar se o serviço foi encontrado
        if (!$service) {
            return response()->json(['error' => 'Serviço não encontrado.'], 404);
        }
    
        // Verificar se o serviço já está fechado
        if ($service->retorno) {
            return response()->json(['error' => 'O serviço precisa de retorno.'], 400);
        }
    
        // Se o serviço estiver aberto e associado a um suporte
        if ($service->support_id) {
            // Encontrar o suporte associado ao serviço
            $support = Support::find($service->support_id);
    
            // Verificar se o suporte foi encontrado
            if ($support) {
                // Atualizar o status de disponibilidade do suporte para livre (true)
                $support->livre = true;
                $support->save();
                
            } else {
                return response()->json(['error' => 'Serviço está sem retorno, mas o suporte não foi encontrado.'], 500);
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'O status do suporte foi atualizado para livre.',
            'data' => $support
        ], Response::HTTP_OK);
    }    

   

    public function findByServiceSupport(Request $request)
    {
    $user = auth()->user();
    $support = $user->support()->first();

    // Verificar se o usuário é do tipo suporte
    if ($user->tipo_funcionario !== 'suporte') {
        return response()->json(['error' => 'Somente usuários suporte podem acessar esta função.'], 403);
    }

    // Buscar os serviços atribuídos ao nome do suporte
    $services = Service::where('support_id', $support->id)->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ], Response::HTTP_OK);
    }

    public function findByUnattendedServiceAreaSupport(Request $request)
    {
    $user = auth()->user();
    
       
    $supports = $user->support; 
    
  
    // Verificar se o usuário é do tipo suporte e está livre
    foreach ($supports as $support)
        if ($user->tipo_funcionario !== 'suporte' || !$support->livre) {
            return response()->json(['error' => 'Somente usuários suporte livres podem acessar esta função.'], 403);
        }

    // Buscar os serviços sem atendimento da área do suporte
    $services = Service::where('retorno', true)
                        ->whereHas('contact', function ($query) use ($support) {
                            $query->where('area_atendimento', $support->area_atuacao);
                        })
                        ->get();                    
                        

    return response()->json([
        'success' => true,
        'data' => $services
    ], Response::HTTP_OK);
    }
    

    public function clientSearched(Request $request)
    {
    $request->validate([
        'data' => 'required|date',
    ]);    
    // Recuperar uma lista de nomes de clientes registrados no dia especificado
    $date = $request->input('date');
    $clients = Contact::whereDate('created_at', $date)
                      ->whereNotNull('nome_cliente')
                      ->distinct()
                      ->pluck('nome_cliente');

    return response()->json([
        'success' => true,
        'data' => $clients
    ]);
    }
    

    public function suportServiceSearched(Request $request)
    {
    $date = $request->input('date');    
    $support = Service::whereDate('created_at', $date)
                    ->whereNotNull('support_id')
                    ->groupBy('support_id')                      
                    ->addSelect(['total' => Service::raw('COUNT(*)')])
                    ->get(['support_id', 'total']);

    return response()->json([
        'success' => true,
        'data' => $support
    ]);
    }
    
    public function areaSearched(Request $request)
    {
   
    $date = $request->input('date');
    $areas = Contact::whereDate('created_at', $date)
                    ->distinct()
                    ->pluck('area_atendimento');    

    return response()->json([
        'success' => true,
        'data' => $areas
    ]);
    }
        
    
    /*public function typeServiceSearched(Request $request)
    {
    $data = $request->input('date')    
    $types = Service::whereDate('created_at', $data)  
                    ->distinct()
                    ->pluck('tipo_servico');  
   
    return response()->json([
        'success' => true,
        'data' => $types
    ]);                
    }  
    
    public function unattendedServiceSearched (Request $request)
    {
    $data = $request->input('date')
    $unattended = Service::whereDate('created_at', $data)  
                        ->where('retorno', true)
                        ->get();

    return response()->json([
        'success' => true,
        'data' => $unattended
    ]);              
    }
*/
    
}



