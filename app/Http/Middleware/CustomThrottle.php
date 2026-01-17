<?php

namespace App\Http\Middleware;

use App\Services\RateLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottle
{
    protected RateLimitService $rateLimitService;

    public function __construct(RateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limitType = 'api.read'): Response
    {
        if (!config('rate-limit.enabled', true)) {
            return $next($request);
        }

        // Parse limit type (e.g., 'api.read', 'api.write', 'auth.login')
        [$type, $operation] = $this->parseLimitType($limitType);

        // Get max attempts based on user role
        $maxAttempts = $this->getMaxAttempts($request, $type, $operation);
        $decayMinutes = $this->getDecayMinutes($type, $operation);

        // Generate unique key for this request
        $key = $this->rateLimitService->resolveRequestSignature($request, $limitType . ':');

        // Check if limit is exceeded
        if ($this->rateLimitService->checkLimit($key, $maxAttempts, $decayMinutes)) {
            // Log violation
            $this->rateLimitService->logViolation($key, $request, $maxAttempts);

            // Return 429 response
            return $this->buildRateLimitResponse($key, $maxAttempts, $limitType);
        }

        // Increment counter
        $this->rateLimitService->hit($key, $decayMinutes);

        // Process request
        $response = $next($request);

        // Add rate limit headers
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->rateLimitService->remaining($key, $maxAttempts),
            $this->rateLimitService->availableIn($key)
        );
    }

    /**
     * Parse limit type into type and operation
     */
    protected function parseLimitType(string $limitType): array
    {
        $parts = explode('.', $limitType);

        return [
            $parts[0] ?? 'api',
            $parts[1] ?? 'read',
        ];
    }

    /**
     * Get max attempts for the request
     */
    protected function getMaxAttempts(Request $request, string $type, string $operation): int
    {
        // For auth endpoints, use fixed limits
        if ($type === 'auth') {
            return config("rate-limit.limits.auth.{$operation}.max_attempts", 5);
        }

        // For API endpoints, use role-based limits
        return $this->rateLimitService->getLimitForUser(
            $request->user(),
            $type,
            $operation
        );
    }

    /**
     * Get decay minutes for the limit type
     */
    protected function getDecayMinutes(string $type, string $operation): int
    {
        if ($type === 'auth') {
            return config("rate-limit.limits.auth.{$operation}.decay_minutes", 1);
        }

        return 1; // Default to 1 minute for API endpoints
    }

    /**
     * Build rate limit exceeded response
     */
    protected function buildRateLimitResponse(string $key, int $maxAttempts, string $limitType): Response
    {
        $retryAfter = $this->rateLimitService->availableIn($key);

        $message = $this->getThrottleMessage($limitType, $retryAfter);

        return response()->json([
            'message' => $message,
            'retry_after' => $retryAfter,
            'limit' => $maxAttempts,
        ], 429)->withHeaders([
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Limit' => $maxAttempts,
                    'X-RateLimit-Remaining' => 0,
                    'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
                ]);
    }

    /**
     * Get appropriate throttle message
     */
    protected function getThrottleMessage(string $limitType, int $retryAfter): string
    {
        if (str_starts_with($limitType, 'auth')) {
            return str_replace(':seconds', $retryAfter, config('rate-limit.messages.auth_throttle'));
        }

        if (str_contains($limitType, 'export')) {
            return config('rate-limit.messages.export_throttle');
        }

        return config('rate-limit.messages.too_many_requests');
    }

    /**
     * Add rate limit headers to response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remaining, int $resetIn): Response
    {
        if (!config('rate-limit.include_headers', true)) {
            return $response;
        }

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds($resetIn)->timestamp);

        return $response;
    }
}
