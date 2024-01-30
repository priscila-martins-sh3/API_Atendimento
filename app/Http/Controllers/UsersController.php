<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class UsersController extends Controller
{
    // Processa o cadastro do usuário
    public function cadastro(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'tipo_funcionario' => 'required|string|max:255',
        ]);

        // Criação do usuário
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'tipo_funcionario' => $request->tipo_funcionario,
        ]);

        // Retorno em JSON
        return response()->json(['message' => 'Cadastro realizado com sucesso!'], 201);
    }  
}    
