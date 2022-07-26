<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // role_name = "user" in LOCAL db
    const ROLE_USER = "56d01e2e-2334-49c0-9469-4419d9cc0a62";

    public function register(Request $request)
    {

        try {

            Log::info('Trying to register a new user');

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => bcrypt($request->password)
            ]);

            // by default, we assign the role 'user' to every new user
            $user->roles()->attach(self::ROLE_USER);

            $token = JWTAuth::fromUser($user);

            Log::info('New user registered: '. $user->email);

            return response()->json(compact('user', 'token'), 201);

        } catch (\Exception $exception) {

            Log::error("Error in registering new user: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error in registering new user'
                ],
                500
            );
        }
    }

    public function login(Request $request)
    {
        try {

            Log::info('User login');

            $input = $request->only('email', 'password');
            $jwt_token = null;

            if (!$jwt_token = JWTAuth::attempt($input)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Email or Password',
                ], Response::HTTP_UNAUTHORIZED);  // = status 401
            }

            return response()->json([
                'success' => true,
                'token' => $jwt_token,
            ]);

        } catch (\Exception $exception) {

            Log::error("Error on login: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error on login'
                ],
                500
            );
        }
    }

    public function myProfile()
    {
        $user = auth()->user();
        Log::info('User ' . $user->email . 'has consulted their personal profile');
        return response()->json($user);
    }

    public function logout()
    {        
        Log::info('Trying log out');

        try {
            
            JWTAuth::invalidate(auth());

            Log::info('Successful log out');

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
            
        } catch (\Exception $exception) {

            Log::error("Error on logout: " . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);  // es como poner un status 500
        }
    }
}
