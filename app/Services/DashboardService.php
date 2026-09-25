<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get dashboard stats, recent data, and role-based members for the given user.
     *
     * @param User $user
     * @return array{stats: array, recentData: array, members: \Illuminate\Database\Eloquent\Collection}
     */
    public function getDashboardData(User $user): array
    {
        $statsKey = "dashboard_stats_" . $user->id;
        $recentKey = "dashboard_recent_" . $user->id;

        if ($user->role === UserRole::ADMIN->value) {
            $stats = Cache::remember($statsKey, now()->addMinutes(10), fn() => [
                'mentors_count' => User::where('role', UserRole::MENTOR->value)->count(),
                'interns_count' => User::where('role', UserRole::INTERN->value)->count(),
                'total_tasks_count' => Task::count(),
            ]);

            $recentData = Cache::remember($recentKey, now()->addMinutes(5), fn() => [
                'recent_mentors' => User::where('role', UserRole::MENTOR->value)
                    ->select('id', 'name', 'email', 'created_at')
                    ->latest()
                    ->take(5)
                    ->get(),
            ]);
        } elseif ($user->role === UserRole::MENTOR->value) {
            $mentorDepartment = $user->department;

            // Department-scoped interns
            $deptInternsQuery = User::where('role', UserRole::INTERN->value);
            if ($mentorDepartment) {
                $deptInternsQuery->where('department', $mentorDepartment);
            }
            $deptInternsCount = (clone $deptInternsQuery)->count();
            $deptInternIds = (clone $deptInternsQuery)->pluck('id');

            // Department-scoped tasks
            $deptTasksQuery = Task::whereIn('intern_id', $deptInternIds);
            $totalDeptTasks = (clone $deptTasksQuery)->count();
            $approvedDeptTasks = (clone $deptTasksQuery)->where('status', TaskStatus::APPROVED->value)->count();
            $submittedDeptTasks = (clone $deptTasksQuery)->where('status', TaskStatus::SUBMITTED->value)->count();
            $pendingDeptTasks = (clone $deptTasksQuery)->whereIn('status', TaskStatus::pendingWorkValues())->count();
            $rejectedDeptTasks = (clone $deptTasksQuery)->whereIn('status', TaskStatus::rejectedValues())->count();

            $completionRate = $totalDeptTasks > 0
                ? round(($approvedDeptTasks / $totalDeptTasks) * 100, 1)
                : 0;

            $approvedPct = $totalDeptTasks > 0 ? round(($approvedDeptTasks / $totalDeptTasks) * 100) : 0;
            $submittedPct = $totalDeptTasks > 0 ? round(($submittedDeptTasks / $totalDeptTasks) * 100) : 0;
            $pendingPct = $totalDeptTasks > 0 ? round(($pendingDeptTasks / $totalDeptTasks) * 100) : 0;
            $rejectedPct = $totalDeptTasks > 0 ? max(0, 100 - ($approvedPct + $submittedPct + $pendingPct)) : 0;

            $stats = Cache::remember($statsKey, now()->addMinutes(5), fn() => [
                'total_assigned_tasks' => Task::where('mentor_id', $user->id)->count(),
                'pending_reviews' => Task::where('mentor_id', $user->id)->where('status', TaskStatus::SUBMITTED->value)->count(),
                'approved_tasks' => Task::where('mentor_id', $user->id)->where('status', TaskStatus::APPROVED->value)->count(),
                'rejected_tasks' => Task::where('mentor_id', $user->id)->whereIn('status', TaskStatus::rejectedValues())->count(),
                'total_interns' => $deptInternsCount,
                'department' => [
                    'name' => $mentorDepartment,
                    'label' => $user->department_label ?? 'Department',
                    'total_interns' => $deptInternsCount,
                    'total_tasks' => $totalDeptTasks,
                    'approved_tasks' => $approvedDeptTasks,
                    'submitted_tasks' => $submittedDeptTasks,
                    'pending_tasks' => $pendingDeptTasks,
                    'rejected_tasks' => $rejectedDeptTasks,
                    'completion_rate' => $completionRate,
                    'approved_pct' => $approvedPct,
                    'submitted_pct' => $submittedPct,
                    'pending_pct' => $pendingPct,
                    'rejected_pct' => $rejectedPct,
                ],
            ]);

            // Tasks approaching deadline in next 24-48 hours where intern hasn't submitted yet
            $upcomingTasks = Task::with(['intern:id,name,email,department'])
                ->where(function ($q) use ($deptInternIds, $user) {
                    $q->whereIn('intern_id', $deptInternIds)
                      ->orWhere('mentor_id', $user->id);
                })
                ->whereNotIn('status', [TaskStatus::SUBMITTED->value, TaskStatus::APPROVED->value])
                ->whereNotNull('deadline')
                ->where('deadline', '>=', now())
                ->where('deadline', '<=', now()->addHours(48))
                ->orderBy('deadline', 'asc')
                ->take(6)
                ->get()
                ->map(function ($task) {
                    $now = now();
                    $deadline = $task->deadline;
                    $diffHours = (int) round($now->diffInHours($deadline, false));
                    $isOverdue = $now->greaterThan($deadline);

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'deadline' => $deadline->toIso8601String(),
                        'deadline_human' => $deadline->diffForHumans(),
                        'formatted_date' => $deadline->format('M d, h:i A'),
                        'hours_remaining' => max(1, $diffHours),
                        'is_overdue' => $isOverdue,
                        'is_urgent' => $diffHours <= 24,
                        'intern' => [
                            'id' => $task->intern?->id,
                            'name' => $task->intern?->name ?? 'Unknown Intern',
                            'email' => $task->intern?->email ?? '',
                            'department' => $task->intern?->department,
                            'department_label' => $task->intern?->department_label,
                        ],
                    ];
                });

            $recentData = Cache::remember($recentKey, now()->addMinutes(2), fn() => [
                'recent_submissions' => Task::with(['intern:id,name,email', 'submission'])
                    ->where('mentor_id', $user->id)
                    ->where('status', TaskStatus::SUBMITTED->value)
                    ->latest()
                    ->take(5)
                    ->get(),
                'upcoming_deadlines' => $upcomingTasks,
            ]);
        } else {
            $stats = Cache::remember($statsKey, now()->addMinutes(10), fn() => [
                'total_my_tasks' => Task::where('intern_id', $user->id)->count(),
                'pending_tasks' => Task::where('intern_id', $user->id)->where('status', TaskStatus::PENDING->value)->count(),
                'submitted_tasks' => Task::where('intern_id', $user->id)->where('status', TaskStatus::SUBMITTED->value)->count(),
                'approved_tasks' => Task::where('intern_id', $user->id)->where('status', TaskStatus::APPROVED->value)->count(),
                'rejected_tasks' => Task::where('intern_id', $user->id)->whereIn('status', TaskStatus::rejectedValues())->count(),
            ]);

            $recentData = Cache::remember($recentKey, now()->addMinutes(5), fn() => [
                'recent_tasks' => Task::with(['mentor:id,name', 'submission'])
                    ->where('intern_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get(),
            ]);
        }

        return [
            'stats' => $stats ?? [],
            'recentData' => $recentData ?? [],
            'members' => $this->userRepository->getMembersForUser($user),
        ];
    }
}
