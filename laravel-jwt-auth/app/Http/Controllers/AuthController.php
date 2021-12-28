<?php
namespace App\Http\Controllers;

use App\Jobs\PasswordResetJob;
use App\Jobs\VerifyUserJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'accountVerify', 'forgotPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all() , [

        'email' => 'required|email', 'password' => 'required|string|min:6', ]);

        if ($validator->fails())
        {
            return response()
                ->json($validator->errors() , 422);
        }

        if (!$token = JWTAuth::attempt($validator->validated()))
        {
            return response()
                ->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all() , ['name' => 'required|string|between:2,100', 'email' => 'required|string|email|max:100|unique:users', 'password' => 'required|string|confirmed|min:6', ]);

        if ($validator->fails())
        {
            return response()
                ->json($validator->errors()
                ->toJson() , 400);
        }

        $user = User::create(array_merge($validator->validated() , ['password' => bcrypt($request->password) ,

        'slug' => Str::random(20) , 'token' => Str::random(20) , 'status' => 'active',

        ]));

        if ($user)
        {
            // Genarate From User Table
            $details = ['name' => $user->name, 'email' => $user->email, 'hashEmail' => Crypt::encryptString($user->email) , 'token' => $user->token, ];
            dispatch(new VerifyUserJobs($details));
        }
        return response()->json(['message' => 'User successfully registered', 'user' => $user], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountVerify($token, $email)

    {
        $user = User::where([['email', Crypt::decryptString($email) ], ['token', $token]])->first();
        if ($user->token == $token)
        {
            $user->update(['verify' => true, 'token' => null]);
            return redirect()
                ->to('http://127.0.0.1:8000/verify/success');
        }
        return redirect()
            ->to('http://127.0.0.1:8000/verify/invalid_token');

    }

    // Forgot Password Seciton


    public function forgotPassword(Request $request)
    {

        $user = User::where('email',$request->email)->first();
        if ($user) {
            $token = Str::random(15);
            $details = [
                'name'=>$user->name,
                'token'=>$token,
                'email'=>$user->email,
                'hashEmail'=>Crypt::encryptString($user->email)
            ];

            if(dispatch(new PasswordResetJob($details))){
               DB::table('password_resets')->insert([

                   'email'=>$user->email,
                   'token'=>$token,
                   'created_at'=>now()
                   
               ]);

            }
        } else {

            return "Invalide Email Address";

        }






    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()
            ->logout();

        return response()
            ->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()
            ->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()
            ->json(auth()
            ->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json(['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => JWTAuth::factory()->getTTL() * 60, 'user' => auth()
            ->user() ]);
    }

}

