<?php

namespace App\Enums;

enum UserRole: string
{

    case ADMIN = 'admin';
    case MENTOR = 'mentor';
    case INTERN = 'intern';

    /** 
     * Check if a role string matches this enum instance.
     */
    public function is(string $role): bool
    {
        return $this->value === $role;
    }
}