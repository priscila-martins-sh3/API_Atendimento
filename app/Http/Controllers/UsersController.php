<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Suporte;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function register(Request $request)
    {
    	// Definir as regras de validação
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'tipo_funcionario' => 'required|string|in:' . User::tiposValidos(),
        ];

        // Se o tipo de funcionário for suporte, exigir o preenchimento da área de atuação
        if ($request->tipo_funcionario === 'suporte') {
            // Verifica se a área de atuação foi fornecida na requisição
            if (!$request->filled('area_atuacao')) {
                return response()->json(['error' => 'O campo área de atuação é obrigatório para usuários de suporte.'], 400);
            }
            
            // Continua com o processo de validação e criação do usuário
            $rules['area_atuacao'] = 'required|string';
            
        } else {
            // Para outros tipos de funcionário, o campo área de atuação não é necessário
            // Removê-lo dos dados da requisição se estiver presente
            $request->request->remove('area_atuacao');
            
        }

        // Validar os dados da requisição
        $validator = Validator::make($request->all(), $rules);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        //Request is valid, create new user
        $user = User::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password),
            'tipo_funcionario' => $request->tipo_funcionario
        ]);
        if ($request->tipo_funcionario === 'suporte') {
            Suporte::create([
                'area_atuacao' => $request->area_atuacao,                
                'user_id' => $user->id,
            ]);
        }
        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }
 	
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
}    
