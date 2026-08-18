<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class InternTaskController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository
    ) {}

    public function index()
    {
        $user = Auth::user();
        if ($user->role !== 'intern') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $tasks = $this->taskRepository->getTasksForIntern($user->id);

        return Inertia::render('Intern/Task', ['tasks' => $tasks]);
    }

    public function submit(Request $request, Task $task)
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

        $this->taskRepository->submitTask($task, $user->id, [
            'explanation' => $request->explanation,
            'tech_stack' => $request->tech_stack,
            'github_link' => $request->github_link,
        ]);

        // Invalidate Redis dashboard cache for Intern & Mentor
        Cache::forget("dashboard_stats_" . $user->id);
        Cache::forget("dashboard_recent_" . $user->id);
        Cache::forget("dashboard_stats_" . $task->mentor_id);
        Cache::forget("dashboard_recent_" . $task->mentor_id);

        return back()->with('success', 'Task submitted successfully.');
    }
}

