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

        return $this->success([
            'center' => new CenterResource($result['center']),
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Registration request submitted successfully. Waiting for Landlord approval.', 201);
    }

    /**
     * Authenticate user and issue Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success(
            new AuthResource($result['user'], $result['token']),
            'Login successful.'
        );
    }

    /**
     * Logout authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Successfully logged out.');
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('center');

        return $this->success(
            new UserResource($user),
            'Profile retrieved successfully.'
        );
    }
}
