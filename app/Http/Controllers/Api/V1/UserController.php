<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // Only Admin can view users
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = User::with(['role', 'section']);

        // Filter by role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        // Only Admin can create users
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'section_id' => 'nullable|exists:sections,id',
            'is_active' => 'boolean',
        ]);

        // Validate section requirement based on role
        $role = Role::findOrFail($validated['role_id']);
        if (in_array($role->name, ['Chef', 'Frontline Sales']) && !isset($validated['section_id'])) {
            return response()->json([
                'message' => 'Section is required for Chef and Sales roles'
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'section_id' => $validated['section_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->load(['role', 'section'])
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Only Admin can view user details
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->load(['role', 'section']);

        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Only Admin can update users
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
            'section_id' => 'nullable|exists:sections,id',
            'is_active' => 'sometimes|boolean',
        ]);

        // Validate section requirement if role is being changed
        if (isset($validated['role_id'])) {
            $role = Role::findOrFail($validated['role_id']);
            if (in_array($role->name, ['Chef', 'Frontline Sales'])) {
                $sectionId = $validated['section_id'] ?? $user->section_id;
                if (!$sectionId) {
                    return response()->json([
                        'message' => 'Section is required for Chef and Sales roles'
                    ], 422);
                }
            }
        }

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->load(['role', 'section'])
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Only Admin can delete users
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get all roles.
     */
    /**
     * Get all roles.
     */
    public function roles()
    {
        $roles = Role::all();
        return response()->json(['data' => $roles]);
    }

    /**
     * Get all sections.
     */
    public function sections()
    {
        $sections = Section::all();
        return response()->json(['data' => $sections]);
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        // Only Admin can toggle status
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        return response()->json([
            'message' => 'User status updated successfully',
            'data' => $user
        ]);
    }
}
