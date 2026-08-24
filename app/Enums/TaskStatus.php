<?php

namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case REJECT = 'reject'; // Legacy backward compatibility

    /**
     * Get rejected status values.
     *
     * @return array<string>
     */
    public static function rejectedValues(): array
    {
        return [self::REJECTED->value, self::REJECT->value];
    }

    /**
     * Get statuses that are still pending work or in progress.
     *
     * @return array<string>
     */
    public static function pendingWorkValues(): array
    {
        return [self::PENDING->value, self::IN_PROGRESS->value];
    }

    /**
     * Normalize status input.
     */
    public static function normalize(string $status): string
    {
        return $status === self::REJECTED->value ? self::REJECT->value : $status;
    }
}
