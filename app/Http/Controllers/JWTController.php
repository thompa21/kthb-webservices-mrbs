<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;  

//för jwt
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use \UnexpectedValueException;
use \DomainException;
use Firebase\JWT\SignatureInvalidException;

class JWTController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function __construct()
    {
        //definiera vilka anrop som behöver nyckel/autentisering
        $this->middleware('auth', ['only' => [
            'index', 'getUserFromToken'
        ]]);
        //Skicka alla anrop till middleware som sätter locale utifrån parameter/header
        $this->middleware('localization');
    }

    public function index(Request $request)
    {
        //Om autentiserad skicka tillbaks ok
        return response()->json([
            'authorized' => true
        ], 200);
    }

    public function getUserFromToken(Request $request)
    {
        $token = $request->get('token');
        $secretKey = base64_decode(env("JWT_SECRET", "missing"));
        $credentials = JWT::decode($token, $secretKey, ['HS512']);
        return response()->json($credentials);
        return response()->json([
            'userid' => ''
        ], 200);
    }
}
?>