<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected UserRepositoryInterface $userRepository,
        protected TaskService $taskService
    ) {}

    public function index(): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::MENTOR->value) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $interns = $this->userRepository->getUsersByRole(UserRole::INTERN->value, ['id', 'name', 'email', 'department']);
        $tasks = $this->taskRepository->getTasksForMentor($user->id);

        return Inertia::render('mentor/Task', [
            'interns' => $interns,
            'tasks' => $tasks,
            'departments' => \App\Enums\Department::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::MENTOR->value) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
        ]);

        $this->taskService->createTask($user->id, $request->only(['intern_id', 'title', 'description', 'deadline']));

        return back()->with('success', 'Task created successfully.');
    }

    public function review(Request $request, Task $task): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::MENTOR->value || $task->mentor_id !== $user->id) {
            return back()->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'status' => 'required|in:approved,reject,rejected',
            'feedback' => 'nullable|string',
        ]);

        $this->taskService->reviewTask($task, $request->status, $request->feedback);

        return back()->with('success', 'Task solution reviewed successfully.');
    }

    public function internsList(): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::MENTOR->value) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $interns = User::where('role', UserRole::INTERN->value)
            ->select('id', 'name', 'email', 'created_at')
            ->withCount([
                'myTasks as total_tasks',
                'myTasks as pending_tasks' => fn($q) => $q->where('status', 'pending'),
                'myTasks as submitted_tasks' => fn($q) => $q->where('status', 'submitted'),
                'myTasks as approved_tasks' => fn($q) => $q->where('status', 'approved'),
                'myTasks as rejected_tasks' => fn($q) => $q->whereIn('status', ['reject', 'rejected']),
            ])
            ->latest()
            ->get();

        return Inertia::render('mentor/Interns', ['interns' => $interns]);
    }

    public function history(): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::MENTOR->value) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $tasks = $this->taskRepository->getTasksForMentor($user->id);

        return Inertia::render('mentor/TaskHistory', ['tasks' => $tasks]);
    }
}
