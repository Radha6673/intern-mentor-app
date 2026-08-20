<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReportController extends Controller
{
    /**
     * Get performance report for a specific intern or overall team summary.
     */
    public function show(Request $request, ?User $user = null)
    {
        $currentUser = Auth::user();

        if (!in_array($currentUser->role, ['admin', 'mentor'])) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // If a specific intern user is requested
        if ($user && $user->id) {
            if ($user->role !== 'intern') {
                return response()->json(['error' => 'User is not an intern.'], 422);
            }

            $report = $this->generateSingleInternReport($user);
            return response()->json($report);
        }

        // Fetch performance reports for all interns
        $interns = User::where('role', 'intern')->get();
        $reports = $interns->map(fn($intern) => $this->generateSingleInternReport($intern));

        $totalInterns = $reports->count();
        $avgCompletion = $totalInterns > 0 ? round($reports->avg('completion_rate_num'), 1) : 0;

        return response()->json([
            'summary' => [
                'total_interns' => $totalInterns,
                'avg_completion_rate' => $avgCompletion . '%',
                'total_tasks_assigned' => $reports->sum('total_tasks'),
                'total_approved_tasks' => $reports->sum('approved_tasks'),
                'generated_at' => now()->toDateTimeString(),
            ],
            'reports' => $reports,
        ]);
    }

    /**
     * Helper to compute performance report for an individual intern.
     */
    private function generateSingleInternReport(User $intern): array
    {
        $tasks = Task::where('intern_id', $intern->id)
            ->with(['mentor:id,name,email'])
            ->latest()
            ->get();

        $totalTasks = $tasks->count();
        $approvedTasks = $tasks->where('status', 'approved')->count();
        $submittedTasks = $tasks->where('status', 'submitted')->count();
        $pendingTasks = $tasks->whereIn('status', ['pending', 'in_progress'])->count();
        $rejectedTasks = $tasks->whereIn('status', ['reject', 'rejected'])->count();

        $completionRateNum = $totalTasks > 0
            ? round(($approvedTasks / $totalTasks) * 100, 1)
            : 0;

        return [
            'intern' => [
                'id' => $intern->id,
                'name' => $intern->name,
                'email' => $intern->email,
                'joined_at' => $intern->created_at ? $intern->created_at->format('M d, Y') : 'N/A',
            ],
            'total_tasks' => $totalTasks,
            'approved_tasks' => $approvedTasks,
            'submitted_tasks' => $submittedTasks,
            'pending_tasks' => $pendingTasks,
            'rejected_tasks' => $rejectedTasks,
            'completion_rate_num' => $completionRateNum,
            'completion_rate' => $completionRateNum . '%',
            'generated_at' => now()->toDateTimeString(),
            'tasks' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'deadline' => $task->deadline ? date('M d, Y', strtotime($task->deadline)) : 'No deadline',
                    'mentor_name' => $task->mentor ? $task->mentor->name : 'N/A',
                    'created_at' => $task->created_at ? $task->created_at->format('M d, Y') : 'N/A',
                ];
            }),
        ];
    }
}
