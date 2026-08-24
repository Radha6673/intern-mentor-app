<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InternTaskController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected TaskService $taskService
    ) {}

    public function index(): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::INTERN->value) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $tasks = $this->taskRepository->getTasksForIntern($user->id);

        return Inertia::render('Intern/Task', ['tasks' => $tasks]);
    }

    public function submit(Request $request, Task $task): RedirectResponse
    {
        $user = Auth::user();
        if ($task->intern_id != $user->id) {
            return back()->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'explanation' => 'required|string|min:10',
            'tech_stack' => 'required|string',
            'github_link' => 'nullable|url',
        ]);

        $this->taskService->submitTask($task, $user->id, [
            'explanation' => $request->explanation,
            'tech_stack' => $request->tech_stack,
            'github_link' => $request->github_link,
        ]);

        return back()->with('success', 'Task submitted successfully.');
    }
}
