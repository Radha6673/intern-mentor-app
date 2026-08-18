<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckOverdueTasksJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Checking for overdue pending tasks....');

        $overdueTasks = Task::with('intern')
            ->where('deadline', '<', now())
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        if ($overdueTasks->isEmpty()) {
            Log::info('No overdue tasks found.');
            return;
        }

        $dispatchedCount = 0;

        foreach ($overdueTasks as $task) {
            if ($task->intern) {
                SendOverdueTaskNotification::dispatch($task);
                $dispatchedCount++;
            }
        }

        Log::info("Successfully dispatched {$dispatchedCount} overdue notification jobs to the queue.");
    }
}
