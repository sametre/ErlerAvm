<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateUserAccessRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('q')->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        $users->getCollection()->transform(fn (User $user) => $this->payload($user));

        return $this->paginated($users);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success([
            'user' => $this->payload($user->load(['roles:id,name', 'permissions:id,name'])),
            'available_roles' => Role::query()->orderBy('name')->pluck('name'),
            'available_permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function updateAccess(UpdateUserAccessRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);

        if (array_key_exists('is_admin', $validated)) {
            $user->forceFill(['is_admin' => $validated['is_admin']])->save();
        }

        return $this->success($this->payload($user->load(['roles:id,name', 'permissions:id,name'])), 'Kullanıcı yetkileri güncellendi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_admin' => $user->isAdmin(),
            'roles' => $user->roles->pluck('name')->values(),
            'direct_permissions' => $user->permissions->pluck('name')->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
