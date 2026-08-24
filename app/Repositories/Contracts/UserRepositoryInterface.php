<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get relevant members for the given authenticated user based on role.
     *
     * @param User $currentUser
     * @return Collection
     */
    public function getMembersForUser(User $currentUser): Collection;

    /**
     * Get users filtering by their role.
     *
     * @param string $role
     * @param array $columns
     * @return Collection
     */
    public function getUsersByRole(string $role, array $columns = ['id', 'name', 'email']): Collection;

    /**
     * Create a new user.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function createUser(array $data);

    /**
     * Delete a user by ID or model instance.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     * Update user role.
     *
     * @param int $id
     * @param string $role
     * @return bool
     */
    public function updateUserRole(int $id, string $role): bool;
}
