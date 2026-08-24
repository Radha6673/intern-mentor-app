<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear dashboard cache entries for specified user IDs.
     *
     * @param int ...$userIds
     */
    public function invalidateDashboardCache(int ...$userIds): void
    {
        foreach ($userIds as $userId) {
            if ($userId <= 0) {
                continue;
            }
            Cache::forget("dashboard_stats_{$userId}");
            Cache::forget("dashboard_recent_{$userId}");
        }
    }
}
