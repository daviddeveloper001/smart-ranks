<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Controllers\Api\V1\ApiControllerV1;
use App\Http\Requests\Api\Auth\LoginUserRequest;

class AuthController extends ApiControllerV1
{
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'message' => 'Authenticated',
            'user' => auth('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 // segundos
        ]);
    }


    public function register(RegisterRequest $request)
    {
        $isAdmin = auth('api')->check() && auth('api')->user()->hasRole('admin');


        $role = $isAdmin && in_array($request->role, ['admin', 'user'])
            ? $request->role
            : 'user';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($role);

        return $this->success('Registration success', [
            'user' => $user,
            'assigned_role' => $role,
        ], Response::HTTP_CREATED);
    }




    public function me()
    {
        return response()->json(auth()->user());
    }


    public function logout(Request $request): JsonResponse
    {
        auth('api')->logout();

        return $this->success('Logout success');
    }



    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
