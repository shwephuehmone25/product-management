<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->auth->register($request->validated());
            return response()->json([
                'message' => 'Registered successfully',
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                ],
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->auth->login($request->validated());
            return response()->json([
                'message' => 'Logged in successfully',
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                ],
            ]);
        } catch (Throwable $e) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 401;
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], $status);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }
}

