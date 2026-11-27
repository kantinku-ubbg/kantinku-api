<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->lower()->toString(),
            'password' => $request->string('password')->toString(),
            'phone' => $request->input('phone'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::created([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registrasi berhasil.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->lower()->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user->tokens()->where('name', 'auth-token')->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logout berhasil.');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->only(['name', 'phone']));

        return ApiResponse::success(new UserResource($user->fresh()), 'Profil berhasil diperbarui.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update(['password' => $request->string('password')->toString()]);
        $user->tokens()->delete();

        return ApiResponse::success(null, 'Password berhasil diubah. Silakan login ulang.');
    }
}
