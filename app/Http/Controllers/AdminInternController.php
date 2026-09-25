<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminInternController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Display a listing of all interns.
     */
    public function index(): Response
    {
        $interns = $this->userRepository->getUsersByRole(UserRole::INTERN->value, ['id', 'name', 'email', 'department', 'created_at']);

        return Inertia::render('Admin/Interns/Index', [
            'interns' => $interns,
            'departments' => \App\Enums\Department::options(),
        ]);
    }

    /**
     * Promote an intern to mentor role.
     */
    public function promote(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::INTERN->value) {
            return back()->with('error', 'Only intern accounts can be promoted to mentor from this section.');
        }

        $this->userRepository->updateUserRole($user->id, UserRole::MENTOR->value);

        return back()->with('success', "Intern '{$user->name}' has been successfully promoted to Mentor! They can now log in as a Mentor.");
    }

    /**
     * Update an intern's department.
     */
    public function updateDepartment(\Illuminate\Http\Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'department' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Enums\Department::values())],
        ]);

        $user->update(['department' => $request->department]);

        return back()->with('success', "Department updated to '{$user->department_label}' for intern '{$user->name}'.");
    }
}
