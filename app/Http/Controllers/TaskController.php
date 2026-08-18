<?php

namespace App\Http\Controllers;

use App\Jobs\SendOverdueTaskNotification;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected UserRepositoryInterface $userRepository
    ) {}

    public function index()
    {
        $user = Auth::user();
        if ($user->role !== 'mentor') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $interns = $this->userRepository->getUsersByRole('intern');
        $tasks = $this->taskRepository->getTasksForMentor($user->id);

        return Inertia::render('mentor/Task', ['interns' => $interns, 'tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'mentor') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
        ]);

        $task = $this->taskRepository->createTask([
            'mentor_id' => $user->id,
            'intern_id' => $request->intern_id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'status' => 'pending',
        ]);

        // Invalidate Redis dashboard cache for Mentor & Intern
        Cache::forget("dashboard_stats_" . $user->id);
        Cache::forget("dashboard_recent_" . $user->id);
        Cache::forget("dashboard_stats_" . $request->intern_id);
        Cache::forget("dashboard_recent_" . $request->intern_id);

        if ($task->deadline) {
            SendOverdueTaskNotification::dispatch($task)->delay($task->deadline);
        }

        return back()->with('success', 'Task created successfully.');
    }

    public function review(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($user->role !== 'mentor' || $task->mentor_id !== $user->id) {
            return back()->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'status' => 'required|in:approved,reject,rejected',
            'feedback' => 'nullable|string',
        ]);

        $status = $request->status === 'rejected' ? 'reject' : $request->status;

        $this->taskRepository->reviewTask($task, $status, $request->feedback);

        // Invalidate Redis dashboard cache for Mentor & Intern
        Cache::forget("dashboard_stats_" . $task->mentor_id);
        Cache::forget("dashboard_recent_" . $task->mentor_id);
        Cache::forget("dashboard_stats_" . $task->intern_id);
        Cache::forget("dashboard_recent_" . $task->intern_id);

        return back()->with('success', 'Task solution reviewed successfully.');
    }

    public function internsList()
    {
        $user = Auth::user();
        if ($user->role !== 'mentor') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $interns = User::where('role', 'intern')
            ->select('id', 'name', 'email', 'created_at')
            ->withCount([
                'myTasks as total_tasks',
                'myTasks as pending_tasks' => function ($query) {
                    $query->where('status', 'pending');
                },
                'myTasks as submitted_tasks' => function ($query) {
                    $query->where('status', 'submitted');
                },
                'myTasks as approved_tasks' => function ($query) {
                    $query->where('status', 'approved');
                },
                'myTasks as rejected_tasks' => function ($query) {
                    $query->whereIn('status', ['reject', 'rejected']);
                },
            ])
            ->latest()
            ->get();

        return Inertia::render('mentor/Interns', ['interns' => $interns]);
    }

    public function history()
    {
        $user = Auth::user();
        if ($user->role !== 'mentor') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $tasks = $this->taskRepository->getTasksForMentor($user->id);

        return Inertia::render('mentor/TaskHistory', ['tasks' => $tasks]);
    }
}

