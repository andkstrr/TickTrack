<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterStoreRequest;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken; // create token saat berhasil login

            return response()->json([
                'message' => 'Login Success',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user) // get user data as resource
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(RegisterStoreRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try {
            $user = New User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Register Success',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 201);
        } catch (Exception $e){
            return response()->json([
                'message' => 'Register Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function data()
    {
        try {
            $user = Auth::user();

            return response()->json([
                'message' => 'Success to get User Data',
                'data' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get User Data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout Success',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Logout Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
