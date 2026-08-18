<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function getTasksForMentor(int $mentorId): Collection
    {
        return Task::with([
            'intern' => function ($query) {
                $query->select('id', 'name', 'email');
            },
            'submission'
        ])
            ->where('mentor_id', $mentorId)
            ->latest()
            ->get();
    }

    public function getTasksForIntern(int $internId): Collection
    {
        return Task::with([
            'mentor' => function ($query) {
                $query->select('id', 'name');
            },
            'submission'
        ])
            ->where('intern_id', $internId)
            ->latest()
            ->get();
    }

    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    public function submitTask(Task $task, int $internId, array $submissionData): TaskSubmission
    {
        $submission = TaskSubmission::updateOrCreate(
            ['task_id' => $task->id],
            [
                'intern_id' => $internId,
                'explanation' => $submissionData['explanation'],
                'tech_stack' => $submissionData['tech_stack'],
                'github_link' => $submissionData['github_link'] ?? null,
            ]
        );

        $task->update(['status' => 'submitted']);

        return $submission;
    }

    public function reviewTask(Task $task, string $status, ?string $feedback = null): Task
    {
        $task->update(['status' => $status]);

        if ($task->submission) {
            $task->submission->update(['feedback' => $feedback]);
        }

        return $task;
    }
}
