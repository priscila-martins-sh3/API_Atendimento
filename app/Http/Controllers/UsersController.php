<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Support;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function register(Request $request)
    {
    	$rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'tipo_funcionario' => 'required|string|in:' . User::tiposValidos(),
        ];
        
        if ($request->tipo_funcionario === 'suporte') {

            if (!$request->filled('area_atuacao')) {
                return response()->json(['error' => 'O campo área de atuação é obrigatório para usuários de suporte.'], 400);
            }
                        
            $rules['area_atuacao'] = 'required|string';
            
        } else {            
            $request->request->remove('area_atuacao');         
        }
       
        $validator = Validator::make($request->all(), $rules);

        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        
        $user = User::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password),
            'tipo_funcionario' => $request->tipo_funcionario
        ]);
        
        if ($request->tipo_funcionario === 'suporte') {
            Support::create([
                'area_atuacao' => $request->area_atuacao,                
                'user_id' => $user->id,
                'livre' => true,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso.',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
       
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //A solicitação é validada
        //Cria token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'As credenciais de login são invalidas.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Não foi possível criar o token.',
                ], 500);
        }
    
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    
    public function logout(Request $request)
    {        
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);
       
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

		
        //A solicitação é validada, faça logout
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'O usuário foi desconectado.'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Desculpe, o usuário não pode ser desconectado.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
}    
