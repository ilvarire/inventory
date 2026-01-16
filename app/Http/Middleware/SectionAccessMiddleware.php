<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SectionAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Ensures section-specific users can only access their own section's data.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Admin and Manager can access all sections
        if ($user->isAdmin() || $user->isManager()) {
            return $next($request);
        }

        // Get section ID from route parameter or request
        $sectionId = $request->route('sectionId')
            ?? $request->route('section')
            ?? $request->input('section_id');

        // If no section specified, allow (will be handled by controller logic)
        if (!$sectionId) {
            return $next($request);
        }

        // Check if user can access this section
        if (!$user->canAccessSection($sectionId)) {
            return response()->json([
                'message' => 'Unauthorized. You can only access your own section.'
            ], 403);
        }

        return $next($request);
    }
}
