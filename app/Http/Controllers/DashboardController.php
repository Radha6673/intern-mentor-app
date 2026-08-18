<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $statsKey = "dashboard_stats_" . $user->id;
        $recentKey = "dashboard_recent_" . $user->id;

        if ($user->role === 'admin') {
            $stats = Cache::remember($statsKey, now()->addMinutes(10), function () {
                return [
                    'mentors_count' => User::where('role', 'mentor')->count(),
                    'interns_count' => User::where('role', 'intern')->count(),
                    'total_tasks_count' => Task::count(),
                ];
            });

            $recentData = Cache::remember($recentKey, now()->addMinutes(5), function () {
                return [
                    'recent_mentors' => User::where('role', 'mentor')
                        ->select('id', 'name', 'email', 'created_at')
                        ->latest()
                        ->take(5)
                        ->get(),
                ];
            });
        } elseif ($user->role === 'mentor') {
            $stats = Cache::remember($statsKey, now()->addMinutes(10), function () use ($user) {
                return [
                    'total_assigned_tasks' => Task::where('mentor_id', $user->id)->count(),
                    'pending_reviews' => Task::where('mentor_id', $user->id)->where('status', 'submitted')->count(),
                    'approved_tasks' => Task::where('mentor_id', $user->id)->where('status', 'approved')->count(),
                    'rejected_tasks' => Task::where('mentor_id', $user->id)->whereIn('status', ['reject', 'rejected'])->count(),
                    'total_interns' => User::where('role', 'intern')->count(),
                ];
            });

            $recentData = Cache::remember($recentKey, now()->addMinutes(5), function () use ($user) {
                return [
                    'recent_submissions' => Task::with(['intern:id,name,email', 'submission'])
                        ->where('mentor_id', $user->id)
                        ->where('status', 'submitted')
                        ->latest()
                        ->take(5)
                        ->get(),
                ];
            });
        } elseif ($user->role === 'intern') {
            $stats = Cache::remember($statsKey, now()->addMinutes(10), function () use ($user) {
                return [
                    'total_my_tasks' => Task::where('intern_id', $user->id)->count(),
                    'pending_tasks' => Task::where('intern_id', $user->id)->where('status', 'pending')->count(),
                    'submitted_tasks' => Task::where('intern_id', $user->id)->where('status', 'submitted')->count(),
                    'approved_tasks' => Task::where('intern_id', $user->id)->where('status', 'approved')->count(),
                    'rejected_tasks' => Task::where('intern_id', $user->id)->whereIn('status', ['reject', 'rejected'])->count(),
                ];
            });

            $recentData = Cache::remember($recentKey, now()->addMinutes(5), function () use ($user) {
                return [
                    'recent_tasks' => Task::with(['mentor:id,name', 'submission'])
                        ->where('intern_id', $user->id)
                        ->latest()
                        ->take(5)
                        ->get(),
                ];
            });
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats ?? [],
            'recentData' => $recentData ?? [],
        ]);
    }
}
