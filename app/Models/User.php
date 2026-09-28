<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    protected $appends = [
        'department_label',
    ];

    public function getDepartmentLabelAttribute(): ?string
    {
        if (empty($this->department)) {
            return null;
        }

        return \App\Enums\Department::tryFrom($this->department)?->label() 
            ?? ucwords(str_replace('_', ' ', $this->department));
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Mentor ke dwara assign kiye gaye sabhi tasks
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'mentor_id');
    }

    // Intern ko miley hue sabhi tasks
    public function myTasks()
    {
        return $this->hasMany(Task::class, 'intern_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, $this->role === 'mentor' ? 'mentor_id' : 'intern_id');
    }

    /**
     * Determine if the user has verified their email address.
     * Admin and Mentors (including dummy seed accounts) always bypass verification.
     */
    public function hasVerifiedEmail(): bool
    {
        if (in_array($this->role, ['admin', 'mentor'])) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Send the email verification notification using the background queue.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (! $this->hasVerifiedEmail()) {
            $this->notify(new \App\Notifications\QueuedVerifyEmail);
        }
    }
}
