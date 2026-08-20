<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function getUsersByRole(string $role, array $columns = ['id', 'name', 'email']): Collection
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

