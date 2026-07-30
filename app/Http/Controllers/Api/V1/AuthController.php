<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return $this->success([
            'user' => $this->mobileUser($user),
            'token' => $this->issueToken($user, $validated['device_name'] ?? 'Mobil cihaz'),
        ], 'Kayıt başarılı.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error('E-posta adresi veya şifre hatalı.', 401);
        }

        return $this->success([
            'user' => $this->mobileUser($user),
            'token' => $this->issueToken($user, $validated['device_name'] ?? 'Mobil cihaz'),
        ], 'Giriş başarılı.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(null, 'Çıkış yapıldı.');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(['user' => $this->mobileUser($request->user())]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mobileUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_admin' => $user->isAdmin(),
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }

    private function issueToken(User $user, string $deviceName): string
    {
        $abilities = ['mobile'];

        if ($user->isAdmin()) {
            $abilities[] = 'admin';
        }

        return $user->createToken($deviceName, $abilities)->plainTextToken;
    }
}
