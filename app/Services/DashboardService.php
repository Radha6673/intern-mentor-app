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
            $stats = Cache::remember($statsKey, now()->addMinutes(10), fn() => [
                'total_assigned_tasks' => Task::where('mentor_id', $user->id)->count(),
                'pending_reviews' => Task::where('mentor_id', $user->id)->where('status', TaskStatus::SUBMITTED->value)->count(),
                'approved_tasks' => Task::where('mentor_id', $user->id)->where('status', TaskStatus::APPROVED->value)->count(),
                'rejected_tasks' => Task::where('mentor_id', $user->id)->whereIn('status', TaskStatus::rejectedValues())->count(),
                'total_interns' => User::where('role', UserRole::INTERN->value)->count(),
            ]);

            $recentData = Cache::remember($recentKey, now()->addMinutes(5), fn() => [
                'recent_submissions' => Task::with(['intern:id,name,email', 'submission'])
                    ->where('mentor_id', $user->id)
                    ->where('status', TaskStatus::SUBMITTED->value)
                    ->latest()
                    ->take(5)
                    ->get(),
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
