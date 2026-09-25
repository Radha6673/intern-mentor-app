<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class PerformanceReportService
{
    /**
     * Generate performance report summary for all interns.
     */
    public function generateTeamReport(): array
    {
        $interns = User::where('role', UserRole::INTERN->value)
            ->with(['myTasks' => function ($q) {
                $q->with('mentor:id,name,email')->latest();
            }])
            ->latest()
            ->get();

        $reports = $interns->map(fn($intern) => $this->formatInternReport($intern, $intern->myTasks));

        $totalInterns = $reports->count();
        $avgCompletion = $totalInterns > 0 ? round($reports->avg('completion_rate_num'), 1) : 0;

        return [
            'summary' => [
                'total_interns' => $totalInterns,
                'avg_completion_rate' => $avgCompletion . '%',
                'total_tasks_assigned' => $reports->sum('total_tasks'),
                'total_approved_tasks' => $reports->sum('approved_tasks'),
                'generated_at' => now()->toDateTimeString(),
            ],
            'reports' => $reports,
        ];
    }

    /**
     * Compute performance metrics for an individual intern.
     */
    public function generateSingleInternReport(User $intern): array
    {
        $tasks = $intern->relationLoaded('myTasks')
            ? $intern->myTasks
            : Task::where('intern_id', $intern->id)
                ->with(['mentor:id,name,email'])
                ->latest()
                ->get();

        return $this->formatInternReport($intern, $tasks);
    }

    /**
     * Format metrics and task collection into report structure.
     */
    protected function formatInternReport(User $intern, $tasks): array
    {
        $totalTasks = $tasks->count();
        $approvedTasks = $tasks->where('status', TaskStatus::APPROVED->value)->count();
        $submittedTasks = $tasks->where('status', TaskStatus::SUBMITTED->value)->count();
        $pendingTasks = $tasks->whereIn('status', TaskStatus::pendingWorkValues())->count();
        $rejectedTasks = $tasks->whereIn('status', TaskStatus::rejectedValues())->count();

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
            })->values(),
        ];
    }
}
