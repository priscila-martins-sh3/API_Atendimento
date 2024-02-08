<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use Illuminate\Http\Request;

class ContactsController extends Controller
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
        $validator = Validator::make($request->all(), [
            'nome_pessoa'=>'required|string',
            'nome_cliente'=>'required|string',
            'area_atendimento'=>'required|string',       
                       
        ]); 
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }      
    
        $contact = Contact::create([        	
        	'nome_pessoa'=>$request->nome_pessoa,
            'nome_cliente'=>$request->nome_cliente,
            'area_atendimento'=>$request->area,                
            
        ]);        

        return response()->json(['success' => true, 'message' => 'Contact criado com sucesso'], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Contacts $contacts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contacts $contacts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contacts $contacts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contacts $contacts)
    {
        //
    }
}
