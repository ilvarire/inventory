<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitService
{
    /**
     * Check if rate limit is exceeded
     */
    public function checkLimit(string $key, int $maxAttempts, int $decayMinutes = 1): bool
    {
        if (!config('rate-limit.enabled', true)) {
            return false;
        }

        $attempts = $this->attempts($key);

        return $attempts >= $maxAttempts;
    }

    /**
     * Increment the counter for a given key
     */
    public function hit(string $key, int $decayMinutes = 1): int
    {
        $cacheKey = $this->getCacheKey($key);

        Cache::add($cacheKey, 0, now()->addMinutes($decayMinutes));

        $attempts = Cache::increment($cacheKey);

        Cache::put($cacheKey . ':timer', now()->addMinutes($decayMinutes)->timestamp, now()->addMinutes($decayMinutes));

        return $attempts;
    }

    /**
     * Get the number of attempts for the given key
     */
    public function attempts(string $key): int
    {
        return Cache::get($this->getCacheKey($key), 0);
    }

    /**
     * Get remaining attempts
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        $attempts = $this->attempts($key);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get the number of seconds until the rate limit resets
     */
    public function availableIn(string $key): int
    {
        $resetTime = Cache::get($this->getCacheKey($key) . ':timer', now()->timestamp);

        return max(0, $resetTime - now()->timestamp);
    }

    /**
     * Reset the number of attempts for the given key
     */
    public function resetAttempts(string $key): void
    {
        Cache::forget($this->getCacheKey($key));
        Cache::forget($this->getCacheKey($key) . ':timer');
    }

    /**
     * Clear all rate limit data for a key
     */
    public function clear(string $key): void
    {
        $this->resetAttempts($key);
    }

    /**
     * Log a rate limit violation
     */
    public function logViolation(string $key, Request $request, int $maxAttempts): void
    {
        if (!config('rate-limit.log_violations', true)) {
            return;
        }

        Log::warning('Rate limit exceeded', [
            'key' => $key,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'max_attempts' => $maxAttempts,
            'attempts' => $this->attempts($key),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the cache key for the given key
     */
    protected function getCacheKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }

    /**
     * Generate a throttle key for the request
     */
    public function resolveRequestSignature(Request $request, string $prefix = ''): string
    {
        if ($user = $request->user()) {
            return $prefix . 'user:' . $user->id;
        }

        return $prefix . 'ip:' . $request->ip();
    }

    /**
     * Get rate limit for user based on role
     */
    public function getLimitForUser(?object $user, string $type, string $operation): int
    {
        $limits = config("rate-limit.limits.{$type}.{$operation}", []);

        if (!$user) {
            return $limits['guest'] ?? 60;
        }

        $role = $user->role->name ?? 'user';

        if (in_array($role, ['Admin', 'Manager'])) {
            return $limits['admin'] ?? $limits['user'] ?? 120;
        }

        return $limits['user'] ?? 60;
    }
}
