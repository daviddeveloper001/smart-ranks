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
        $request->validated($request->all());

        if (! Auth::attempt($request->only('email', 'password'))) {;
            return $this->error('Credentials not match', 401);
        }

        $user = User::firstWhere('email', $request->email);

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken(
                    'API token for' . $user->email,
                    ['*'],
                    now()->addMonth()
                )->plainTextToken
            ]
        );
    }

    public function register(RegisterRequest $request)
    {
        $isAdmin = auth()->check() && auth()->user()->hasRole('admin');

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
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Sesión cerrada correctamente');
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
