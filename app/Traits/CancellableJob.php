<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait CancellableJob
{
    /**
     * Check if the current job execution has been flagged for cancellation.
     *
     * @param string|int|null $customKey Optional identifier (e.g., task ID)
     * @return bool
     */
    public function isCancelled(string|int|null $customKey = null): bool
    {
        $jobName = class_basename(static::class);

        // 1. Check by Queue Job ID if available
        if (isset($this->job) && method_exists($this->job, 'getJobId') && $this->job->getJobId()) {
            $jobIdKey = 'cancel_job_' . $this->job->getJobId();
            if (Cache::has($jobIdKey)) {
                Log::warning("Job [{$jobName}] with Job ID [{$this->job->getJobId()}] was cancelled via Cache key.");
                return true;
            }
        }

        // 2. Check by Job Class name (cancels all instances of this Job class)
        $classKey = 'cancel_job_' . $jobName;
        if (Cache::has($classKey)) {
            Log::warning("Job [{$jobName}] was cancelled via class-level Cache key.");
            return true;
        }

        // 3. Check by custom identifier (e.g. task_5)
        if ($customKey !== null) {
            $customCacheKey = 'cancel_job_' . $customKey;
            if (Cache::has($customCacheKey)) {
                Log::warning("Job [{$jobName}] for identifier [{$customKey}] was cancelled via custom Cache key.");
                return true;
            }
        }

        return false;
    }

    /**
     * Send a cancellation signal for a job ID, class name, or custom key.
     *
     * @param string|int $key Job ID, Job class name, or custom key (e.g., 'task_5')
     * @param int $ttlInSeconds Cache expiration time in seconds (default: 1 hour)
     */
    public static function cancel(string|int $key, int $ttlInSeconds = 3600): void
    {
        $cacheKey = 'cancel_job_' . $key;
        Cache::put($cacheKey, true, $ttlInSeconds);
        Log::info("Cancellation signal set for key: [{$cacheKey}] (TTL: {$ttlInSeconds}s)");
    }

    /**
     * Clear a previously set cancellation signal.
     */
    public static function clearCancelSignal(string|int $key): void
    {
        $cacheKey = 'cancel_job_' . $key;
        Cache::forget($cacheKey);
        Log::info("Cancellation signal cleared for key: [{$cacheKey}]");
    }
}
