<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCenterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\CenterResource;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new Center Admin application.
     */
    public function register(RegisterCenterRequest $request): JsonResponse
    {
        $result = $this->authService->registerCenterAdmin($request->validated());

        return response()->json([
            'message' => 'Registration request submitted successfully. Waiting for Landlord approval.',
            'center' => new CenterResource($result['center']),
            'user' => new UserResource($result['user']),
        ], 201);
    }

    /**
     * Authenticate user and issue Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json(
            new AuthResource($result['user'], $result['token'])
        );
    }

    /**
     * Logout authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('center');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
