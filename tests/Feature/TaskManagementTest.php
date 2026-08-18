<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_assign_task_to_intern(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $response = $this->actingAs($mentor)->post(route('mentor.tasks.store'), [
            'intern_id' => $intern->id,
            'title' => 'Build API Endpoint',
            'description' => 'Create a REST API for managing users.',
            'deadline' => now()->addDays(3)->toDateTimeString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', [
            'mentor_id' => $mentor->id,
            'intern_id' => $intern->id,
            'title' => 'Build API Endpoint',
            'status' => 'pending',
        ]);
    }

    public function test_intern_cannot_assign_tasks(): void
    {
        $intern1 = User::factory()->create(['role' => 'intern']);
        $intern2 = User::factory()->create(['role' => 'intern']);

        $response = $this->actingAs($intern1)->post(route('mentor.tasks.store'), [
            'intern_id' => $intern2->id,
            'title' => 'Unauthorized Task',
            'description' => 'This should fail.',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_intern_can_submit_task_solution(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $task = Task::create([
            'mentor_id' => $mentor->id,
            'intern_id' => $intern->id,
            'title' => 'Write Unit Tests',
            'description' => 'Add feature tests for tasks.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($intern)->post(route('intern.tasks.submit', $task->id), [
            'explanation' => 'Implemented comprehensive PHPUnit feature test cases.',
            'tech_stack' => 'Laravel, PHPUnit',
            'github_link' => 'https://github.com/example/repo',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('task_submissions', [
            'task_id' => $task->id,
            'intern_id' => $intern->id,
            'explanation' => 'Implemented comprehensive PHPUnit feature test cases.',
            'tech_stack' => 'Laravel, PHPUnit',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'submitted',
        ]);
    }

    public function test_assigning_task_dispatches_overdue_job_to_queue(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $response = $this->actingAs($mentor)->post(route('mentor.tasks.store'), [
            'intern_id' => $intern->id,
            'title' => 'Scheduled Task',
            'description' => 'Test job dispatch on creation.',
            'deadline' => now()->addHours(5)->toDateTimeString(),
        ]);

        $response->assertSessionHasNoErrors();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendOverdueTaskNotification::class);
    }

    public function test_check_overdue_tasks_job_dispatches_notifications(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $task = Task::create([
            'mentor_id' => $mentor->id,
            'intern_id' => $intern->id,
            'title' => 'Overdue Task',
            'description' => 'This task is overdue.',
            'deadline' => now()->subDay(),
            'status' => 'pending',
        ]);

        (new \App\Jobs\CheckOverdueTasksJob())->handle();

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $intern,
            \App\Notifications\deadlineOverdue::class
        );
    }

    public function test_mentor_can_approve_task_submission_with_feedback(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $task = Task::create([
            'mentor_id' => $mentor->id,
            'intern_id' => $intern->id,
            'title' => 'Review API Task',
            'description' => 'Build login API.',
            'status' => 'submitted',
        ]);

        $submission = \App\Models\TaskSubmission::create([
            'task_id' => $task->id,
            'intern_id' => $intern->id,
            'explanation' => 'Completed login API using Sanctum.',
            'tech_stack' => 'Laravel, Sanctum',
        ]);

        $response = $this->actingAs($mentor)->post(route('mentor.tasks.review', $task->id), [
            'status' => 'approved',
            'feedback' => 'Excellent work! Code is well structured.',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('task_submissions', [
            'id' => $submission->id,
            'feedback' => 'Excellent work! Code is well structured.',
        ]);
    }

    public function test_mentor_can_reject_task_submission_with_feedback(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $task = Task::create([
            'mentor_id' => $mentor->id,
            'intern_id' => $intern->id,
            'title' => 'Fix Bug',
            'description' => 'Fix database memory leak.',
            'status' => 'submitted',
        ]);

        $submission = \App\Models\TaskSubmission::create([
            'task_id' => $task->id,
            'intern_id' => $intern->id,
            'explanation' => 'Attempted to clear cache.',
            'tech_stack' => 'Redis',
        ]);

        $response = $this->actingAs($mentor)->post(route('mentor.tasks.review', $task->id), [
            'status' => 'reject',
            'feedback' => 'Issue still persists in bulk inserts. Please re-check.',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'reject',
        ]);

        $this->assertDatabaseHas('task_submissions', [
            'id' => $submission->id,
            'feedback' => 'Issue still persists in bulk inserts. Please re-check.',
        ]);
    }

    public function test_mentor_can_view_interns_list_page(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'John Intern']);

        $response = $this->actingAs($mentor)->get(route('mentor.interns.index'));

        $response->assertStatus(200);
        $response->assertSee('John Intern');
    }

    public function test_intern_cannot_view_mentor_interns_list_page(): void
    {
        $intern = User::factory()->create(['role' => 'intern']);

        $response = $this->actingAs($intern)->get(route('mentor.interns.index'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_mentor_can_view_task_history_page(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $task = Task::create([
            'mentor_id' => $mentor->id,
            'intern_id' => $intern->id,
            'title' => 'History Task Title',
            'description' => 'Test history page.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mentor)->get(route('mentor.tasks.history'));

        $response->assertStatus(200);
        $response->assertSee('History Task Title');
    }

    public function test_intern_cannot_view_mentor_task_history_page(): void
    {
        $intern = User::factory()->create(['role' => 'intern']);

        $response = $this->actingAs($intern)->get(route('mentor.tasks.history'));

        $response->assertRedirect(route('dashboard'));
    }
}

