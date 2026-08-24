<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Notifications\DeadlineOverdueNotification;
use App\Traits\CancellableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOverdueTaskNotification implements ShouldQueue
{
    use CancellableJob, InteractsWithQueue, Queueable, SerializesModels;

    public Task $task;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->isCancelled("task_{$this->task->id}")) {
            Log::warning("SendOverdueTaskNotification execution aborted for task ID {$this->task->id} due to cancel signal.");
            return;
        }

        // Reload task to check fresh status
        $this->task->refresh();

        // Check if task is still in pending or in_progress status
        if (!in_array($this->task->status, TaskStatus::pendingWorkValues())) {
            Log::info("Skipping overdue notification for task ID {$this->task->id}: status is '{$this->task->status}'.");
            return;
        }

        $intern = $this->task->intern;

        if ($intern) {
            $intern->notify(new DeadlineOverdueNotification($this->task));
            Log::info("Queued overdue notification sent to intern ID {$intern->id} ({$intern->email}) for task '{$this->task->title}' (ID: {$this->task->id}).");
        } else {
            Log::warning("Task ID {$this->task->id} has no assigned intern. Skipping overdue alert.");
        }
    }
}
