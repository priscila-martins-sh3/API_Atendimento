<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        $data = $request->only('nome_pessoa', 'nome_cliente', 'area', 'tipo_atendimento','nome_suporte', 'retorno');
        $validator = Validator::make($data, [
            'nome_pessoa'=>'required|string',
            'nome_cliente'=>'required|string',
            'area'=>'required|string',
            'tipo_atendimento'=>'required|string',
            'nome_suporte'=>'required|string',
            'retorno'=>'required|boolean'
        ]); 

        //Enviar resposta com falha se a solicitação não for válida
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $service = Service::create([        	
        	'nome_pessoa'=>$request->nome_pessoa,
            'nome_cliente'=>$request->nome_cliente,
            'area'=>$request->area,
            'tipo_atendimento'=>$request->tipo_atendimento,
            'nome_suporte'=>$request->nome_suporte,
            'retorno'=>$request->retorno
        ]);
    }
  
    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }
}
