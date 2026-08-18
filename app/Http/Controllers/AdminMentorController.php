<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class AdminMentorController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Display a listing of all mentors and the creation form.
     */
    public function index()
    {
        $mentors = $this->userRepository->getUsersByRole('mentor', ['id', 'name', 'email', 'created_at']);

        return Inertia::render('Admin/Mentors/Index', [
            'mentors' => $mentors,
        ]);
    }

    /**
     * Store a newly created mentor in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $this->userRepository->createUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mentor',
        ]);

        return back()->with('success', 'Mentor added successfully! They can now login with their email and password.');
    }

    /**
     * Remove the specified mentor from storage.
     */
    public function destroy(User $user)
    {
        if ($user->role !== 'mentor') {
            return back()->with('error', 'Only mentor accounts can be deleted from this section.');
        }

        $this->userRepository->deleteUser($user->id);

        return back()->with('success', 'Mentor deleted successfully.');
    }
}
