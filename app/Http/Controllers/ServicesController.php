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
            'retorno' => 'boolean', 
            'informacoes' => 'string|nullable',
            'support_id' => 'integer|nullable',
            'contact_id' => 'integer',
            
        ]); 

        //Enviar resposta com falha se a solicitação não for válida
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $suporteDisponivel = Suporte::where('area_atuacao', $request->area)
            ->where('livre', true)
            ->pluck('name')
            ->toArray();

        // Se houver suportes disponíveis, validar se o campo nome_suporte foi preenchido
        if (count($suportesDisponiveis) > 0) {
            $validator = Validator::make($request->all(), [
            'nome_suporte' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'O campo nome do suporte é obrigatório.'], 400);
        }
    }
        $service = Service::create([      	
        	    
            'tipo_servico'=>$request->tipo_atendimento,            
            'support_id' => count($suportesDisponiveis) > 0 ? $request->nome_suporte : null,
            'retorno' => count($suportesDisponiveis) == 0 ? true : false,
            'informacoes' => $request->informacoes,
            'contact_id' =>
        ]);

        // Se um suporte foi escolhido, alterar o atributo 'livre' para false
        if ($service->nome_suporte) {
            // Encontrar o ID do usuário associado ao suporte
            $userId = User::where('name', $service->nome_suporte)->value('id');
        
            // Atualizar o atributo 'livre' para false para o suporte associado ao usuário
            Suporte::where('user_id', $userId)->update(['livre' => false]);
        }

        ??return response()->json(['success' => true, 'message' => 'Serviço criado com sucesso'], Response::HTTP_OK);
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
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)    
    {
        // Validar os dados da requisição
        $validator = Validator::make($request->all(), [
            'nome_pessoa' => 'required|string',
            'nome_cliente' => 'required|string',
            'area_atendimento' => 'required|string',
            'tipo_atendimento'=>'required|string|in:' . Service::tiposValidosAtendimento(),
            'nome_suporte' => 'string|nullable',
            'retorna_ligacao' => 'required|boolean',
            'informacoes' => 'string|nullable',
            'data' => 'required|date',
            'hora' => 'nullable', 
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
            'message' => 'Service updated successfully',
            'data' => $service
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }

    public function findBySupportName(Request $request)
    {
    $user = auth()->user();

    // Verificar se o usuário é do tipo suporte
    if ($user->tipo_funcionario !== 'suporte') {
        return response()->json(['error' => 'Somente usuários suporte podem acessar esta função.'], 403);
    }

    // Buscar os serviços atribuídos ao nome do suporte
    $services = Service::where('nome_suporte', $user->name)->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ], Response::HTTP_OK);
    }

    public function findByAreaUnattendedService(Request $request)
    {
    $user = auth()->user();

    // Verificar se o usuário é do tipo suporte e está livre
    if ($user->tipo_funcionario !== 'suporte' || !$user->suporte->livre) {
        return response()->json(['error' => 'Somente usuários suporte livres podem acessar esta função.'], 403);
    }

    // Buscar os serviços sem atendimento da área do suporte
    $services = Service::where('retorno', true)
                        ->where('area_atendimento', $user->suporte->area_atuacao)
                        ->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ], Response::HTTP_OK);
    }

    public function clientService (Request $request)
    {
    $user = auth()->user()
    if ($user->tipo_funcionario !== 'gerente') {
        return response()->json(['error' => 'Somente usuário gerente podem acessar esta função.'], 403);
    }   
   
    }

    public function clientSearched(Request $request)
    {
    // Recuperar uma lista de nomes de clientes registrados no dia especificado
    $date = $request->input('date');
    $clients = Service::whereDate('created_at', $date)
                      ->whereNotNull('nome_cliente')
                      ->distinct()
                      ->pluck('nome_cliente');

    return response()->json([
        'success' => true,
        'data' => $clients
    ]);
    }

    public function servicesByClient(Request $request)
    {
    //Recuperar os serviços associados a um cliente específico
    $clientName = $request->input('client_name');
    $services = Service::where('nome_cliente', $clientName)
                       ->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ]);
    }

    public function suportServicesSearched(Request $request)
    {
    $date = $request->input('date');    
    $suport = Service::whereDate('created_at', $date)
                    ->whereNotNull('nome_suporte')
                    ->groupBy('nome_suporte')                      
                    ->addSelect(['total' => Service::raw('COUNT(*)')])
                    ->get(['nome_suporte', 'total']);

    return response()->json([
        'success' => true,
        'data' => $suport
    ]);
    }

    public function servicesBySuport(Request $request)
    {
   
    $suportName = $request->input('suport_name');
    $services = Service::where('nome_suporte', $suportName)
                       ->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ]);
    
    }

    public function areasSearched(Request $request)
    {
    $date = $request->input('date');
    $areas = Service::whereDate('created_at', $date)
                    ->distinct()
                    ->pluck('area');    

    return response()->json([
        'success' => true,
        'data' => $areas
    ]);
    }

    public function servicesByAreas(Request $request)
    {
   
    $areaName = $request->input('area_name');
    $services = Service::where('area', $areaName)
                       ->get();

    return response()->json([
        'success' => true,
        'data' => $services
    ]);
    }

    public function typesServiceSearched(Request $request)
    {
    $data = $request->input('date')    
    $types = Service::whereDate('created_at', $data)  
                    ->distinct()
                    ->pluck('tipo_atendimento');  

    return response()->json([
        'success' => true,
        'data' => $types
    ]);                
    } 

    public function servicesByType(Request $request)
    {
   
    $typeName = $request->input('type_name');
    $services = Service::where('tipo_atendimento', $typeName)
                       ->get();

    return response()->json([
        'success' => true,
        'data' => $services
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



