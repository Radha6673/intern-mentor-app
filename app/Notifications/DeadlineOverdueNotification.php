<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DeadlineOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get notification channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get mail representation of the notification.
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadlineFormatted = $this->task->deadline
            ? ($this->task->deadline instanceof Carbon ? $this->task->deadline->format('d M Y, h:i A') : Carbon::parse($this->task->deadline)->format('d M Y, h:i A'))
            : 'N/A';

        return (new MailMessage)
            ->error()
            ->subject('⚠️ Action Required: Task Deadline Overdue!')
            ->greeting("Hello {$notifiable->name},")
            ->line("Aapke task **'{$this->task->title}'** ki deadline pass ho chuki hai aur aapne abhi tak solution submit nahi kiya hai.")
            ->line("Deadline: " . $deadlineFormatted)
            ->action('Submit Solution Now', url('/intern/tasks'))
            ->line('Kripya jald se jald apna task complete karke submit karein.');
    }

    /**
     * Get array representation for database notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => "Task '{$this->task->title}' ki deadline pass ho chuki hai. Kripya solution submit karein.",
            'deadline' => $this->task->deadline,
        ];
    }
}
