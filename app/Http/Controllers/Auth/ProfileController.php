<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile($request->user(), $request->validated());

        return $this->success(
            new UserResource($user),
            'Profile updated successfully.'
        );
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->profileService->changePassword($request->user(), $request->validated());

        return $this->success(
            null,
            'Password changed successfully.'
        );
    }
}
