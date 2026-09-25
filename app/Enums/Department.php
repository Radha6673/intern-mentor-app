<?php

namespace App\Enums;

enum Department: string
{
    case WEB_DEVELOPER = 'web_developer';
    case ANDROID_DEVELOPER = 'android_developer';
    case IOS_DEVELOPER = 'ios_developer';
    case DEVOPS = 'devops';
    case AI_DEVELOPER = 'ai_developer';
    case BUSINESS_ANALYST = 'business_analyst';
    case DATA_ANALYST = 'data_analyst';

    /**
     * Get user-friendly display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::WEB_DEVELOPER => 'Web Developer',
            self::ANDROID_DEVELOPER => 'Android Developer',
            self::IOS_DEVELOPER => 'iOS Developer',
            self::DEVOPS => 'DevOps',
            self::AI_DEVELOPER => 'AI Developer',
            self::BUSINESS_ANALYST => 'Business Analyst',
            self::DATA_ANALYST => 'Data Analyst',
        };
    }

    /**
     * Get an array of all department string values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get department options with value and label for UI dropdowns and filters.
     *
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn(self $dept) => [
            'value' => $dept->value,
            'label' => $dept->label(),
        ], self::cases());
    }
}
