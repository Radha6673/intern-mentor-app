<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminInternController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Display a listing of all interns.
     */
    public function index()
    {
        $interns = $this->userRepository->getUsersByRole('intern', ['id', 'name', 'email', 'created_at']);

        return Inertia::render('Admin/Interns/Index', [
            'interns' => $interns,
        ]);
    }

    /**
     * Promote an intern to mentor role.
     */
    public function promote(User $user)
    {
        if ($user->role !== 'intern') {
            return back()->with('error', 'Only intern accounts can be promoted to mentor from this section.');
        }

        $this->userRepository->updateUserRole($user->id, 'mentor');

        return back()->with('success', "Intern '{$user->name}' has been successfully promoted to Mentor! They can now log in as a Mentor.");
    }
}
