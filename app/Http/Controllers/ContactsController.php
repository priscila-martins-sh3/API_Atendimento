<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::all();
        return response()->json(['success' => true, 'data' => $contacts], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
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
        
        return response()->json([
            'success' => true,
            'message' => 'Contato criado com sucesso',
            'data' => $contact
            ], Response::HTTP_OK);
    }
   
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $contact], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'nome_pessoa'=>'required|string',
            'nome_cliente'=>'required|string',
            'area_atendimento'=>'required|string',       
                       
        ]); 
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }    
        
        $contact->update($request->all());
    
        return response()->json([
            'success' => true,
            'message' => 'Contato atualizado com sucesso',
            'data' => $contact
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Contato deletado com sucesso'
        ], Response::HTTP_OK);
    }

    public function restore($id)
    {
        $contact = Contact::onlyTrashed()->findOrFail($id); 
        $contact->restore(); 

        return response()->json([
            'success' => true,
            'message' => 'Contato restaurado com sucesso'
    ], Response::HTTP_OK);        
    }
}
