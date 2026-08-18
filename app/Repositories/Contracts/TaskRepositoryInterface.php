<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use App\Models\TaskSubmission;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * Get all tasks created by a specific mentor with intern and submission relations.
     *
     * @param int $mentorId
     * @return Collection
     */
    public function getTasksForMentor(int $mentorId): Collection;

    /**
     * Get all tasks assigned to a specific intern with mentor and submission relations.
     *
     * @param int $internId
     * @return Collection
     */
    public function getTasksForIntern(int $internId): Collection;

    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function createTask(array $data): Task;

    /**
     * Submit or update a submission for a task.
     *
     * @param Task $task
     * @param int $internId
     * @param array $submissionData
     * @return TaskSubmission
     */
    public function submitTask(Task $task, int $internId, array $submissionData): TaskSubmission;

    /**
     * Review a task submission by updating its status and optional feedback.
     *
     * @param Task $task
     * @param string $status
     * @param string|null $feedback
     * @return Task
     */
    public function reviewTask(Task $task, string $status, ?string $feedback = null): Task;
}
