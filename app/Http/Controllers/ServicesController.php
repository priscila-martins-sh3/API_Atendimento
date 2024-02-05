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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.   
     */
    public function store(Request $request)
    {
        //Validate data
        //$data = $request->only('nome_pessoa', 'nome_cliente', 'area', 'tipo_atendimento','nome_suporte', 'retorno');
        $validator = Validator::make($request->all(), [
            'nome_pessoa'=>'required|string',
            'nome_cliente'=>'required|string',
            'area'=>'required|string',
            'tipo_atendimento'=>'required|string|in:' . Service::tiposValidosAtendimento(),
            
            'retorno' => 'boolean', 
            'informacoes' => 'string|nullable',
            'data_service' => 'required|date',
            'hora_service' => 'date_format:H:i|nullable'
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
        	'nome_pessoa'=>$request->nome_pessoa,
            'nome_cliente'=>$request->nome_cliente,
            'area'=>$request->area,
            'tipo_atendimento'=>$request->tipo_atendimento,            
            'nome_suporte' => count($suportesDisponiveis) > 0 ? $request->nome_suporte : null,
            'retorno' => count($suportesDisponiveis) == 0 ? true : false,
            'informacoes' => $request->informacoes,
            'data_servico' => $request->data_servico,
            'hora_servico' => $request->hora_servico,
        ]);

        // Se um suporte foi escolhido, alterar o atributo 'livre' para false
        if ($service->nome_suporte) {
            // Encontrar o ID do usuário associado ao suporte
            $userId = User::where('name', $service->nome_suporte)->value('id');
        
            // Atualizar o atributo 'livre' para false para o suporte associado ao usuário
            Suporte::where('user_id', $userId)->update(['livre' => false]);
        }

        return response()->json(['success' => true, 'message' => 'Serviço criado com sucesso'], Response::HTTP_OK);
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
}

