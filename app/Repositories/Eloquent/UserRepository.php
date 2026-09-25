<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Map current logged-in user role -> targeted opposite member role
     */
    protected array $roleTargetMap = [
        UserRole::INTERN->value => UserRole::MENTOR->value, // Intern receives Mentors list
        UserRole::MENTOR->value => UserRole::INTERN->value, // Mentor receives Interns list
    ];

    /**
     * Get relevant members for the logged-in user based on their role.
     */
    public function getMembersForUser(User $currentUser): Collection
    {
        if ($currentUser->role === UserRole::ADMIN->value) {
            return User::where('id', '!=', $currentUser->id)
                ->select(['id', 'name', 'email', 'role', 'department', 'created_at'])
                ->latest()
                ->get();
        }

        $targetRole = $this->roleTargetMap[$currentUser->role] ?? null;

        if (!$targetRole) {
            return new Collection();
        }

        return User::where('role', $targetRole)
            ->select(['id', 'name', 'email', 'role', 'department', 'created_at'])
            ->latest()
            ->get();
    }

    public function getUsersByRole(string $role, array $columns = ['id', 'name', 'email', 'department']): Collection
    {
        return User::where('role', $role)->select($columns)->latest()->get();
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

    public function updateUserRole(int $id, string $role): bool
    {
        $user = User::findOrFail($id);
        return $user->update(['role' => $role]);
    }
}

