<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Suporte;
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
        	    
            'tipo_servico'=> $request->tipo_atendimento,        
            'retorno' => $request->retorno,
            'informacoes' => $request->informacoes,
            'support_id' => $request->support_id,
            'contact_id' => $request->contact_id,
        ]);
              
        if ($request->support_id != null){
            $support = Support::where('id', $request->support_id)->get();
            $support->update(['livre' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Serviço criado com sucesso',
            'data' => $service
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
        $support->update(['livre' => false]);
    
        return response()->json([
            'success' => true,
            'message' => 'Serviço associado a um suporte com sucesso',
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





    public function findByServiceSupport(Request $request)
    {
    $user = auth()->user();

    // Verificar se o usuário é do tipo suporte
    if ($user->tipo_funcionario !== 'suporte') {
        return response()->json(['error' => 'Somente usuários suporte podem acessar esta função.'], 403);
    }

    // Buscar os serviços atribuídos ao nome do suporte
    $services = Service::where('support_id', $user->support->id)->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ], Response::HTTP_OK);
    }

    public function findByUnattendedServiceAreaSupport(Request $request)
    {
    $user = auth()->user();

    // Verificar se o usuário é do tipo suporte e está livre
    if ($user->tipo_funcionario !== 'suporte' || !$user->suporte->livre) {
        return response()->json(['error' => 'Somente usuários suporte livres podem acessar esta função.'], 403);
    }

    // Buscar os serviços sem atendimento da área do suporte
    $services = Service::where('retorno', true)
                        ->whereHas('contact', function ($query) use ($user) {
                            $query->where('area_atendimento', $user->suporte->area_atuacao);
                        })
                        ->get();                    
                        

    return response()->json([
        'success' => true,
        'data' => $services
    ], Response::HTTP_OK);
    }
    

    public function clientSearched(Request $request)
    {
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
    

    public function suportServicesSearched(Request $request)
    {
    $date = $request->input('date');    
    $support = Service::whereDate('created_at', $date)
                    ->where('support_id')
                    ->groupBy('support_id')                      
                    ->addSelect(['total' => Service::raw('COUNT(*)')])
                    ->get(['support_id', 'total']);

    return response()->json([
        'success' => true,
        'data' => $support
    ]);
    }
    
    public function areasSearched(Request $request)
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
        
    
    public function typesServiceSearched(Request $request)
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

    
}



