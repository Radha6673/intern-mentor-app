<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Jobs\SendOverdueTaskNotification;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskService
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected CacheService $cacheService
    ) {}

    /**
     * Create a new task and dispatch overdue job if a deadline is set.
     */
    public function createTask(int $mentorId, array $data): Task
    {
        $task = $this->taskRepository->createTask([
            'mentor_id' => $mentorId,
            'intern_id' => $data['intern_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'deadline' => $data['deadline'] ?? null,
            'status' => TaskStatus::PENDING->value,
        ]);

        $this->cacheService->invalidateDashboardCache($mentorId, (int) $data['intern_id']);

        if ($task->deadline) {
            SendOverdueTaskNotification::dispatch($task)->delay($task->deadline);
        }

        return $task;
    }

    /**
     * Submit solution for a task by an intern.
     */
    public function submitTask(Task $task, int $internId, array $submissionData): TaskSubmission
    {
        $submission = $this->taskRepository->submitTask($task, $internId, $submissionData);

        $this->cacheService->invalidateDashboardCache($internId, (int) $task->mentor_id);

        return $submission;
    }

    /**
     * Review a submitted task.
     */
    public function reviewTask(Task $task, string $status, ?string $feedback = null): Task
    {
        $normalizedStatus = TaskStatus::normalize($status);

        $reviewedTask = $this->taskRepository->reviewTask($task, $normalizedStatus, $feedback);

        $this->cacheService->invalidateDashboardCache((int) $task->mentor_id, (int) $task->intern_id);

        return $reviewedTask;
    }
}
