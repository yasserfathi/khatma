<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === User::ROLE_ADMIN) {
            return User::where('created_by', $user->id)->get();
        }
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('created_by', $request->user()?->id);
                }),
            ],
            'email' => 'nullable|email|unique:users,email',
            'phone' => ['required', 'string', 'max:20'],
            'active' => 'boolean',
            'group_numbers' => 'nullable|array',
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|integer|in:1,2',
        ]);

        if (empty($validated['password'])) {
            $validated['password'] = '123456';
        }

        $validated['password'] = Hash::make($validated['password']);

        // Audit: who added this user?
        if ($request->user()) {
            $validated['created_by'] = $request->user()->id;
        }

        // Default role is User (2) if not specified
        if (!isset($validated['role'])) {
            $validated['role'] = User::ROLE_USER;
        }

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === User::ROLE_ADMIN) {
            if ($user->created_by !== $currentUser->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === User::ROLE_ADMIN) {
            if ($user->created_by !== $currentUser->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users')->where(function ($query) use ($user) {
                    return $query->where('created_by', $user->created_by);
                })->ignore($user->id),
            ],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'group_numbers' => 'nullable|array',
            'password' => 'nullable|string|min:8',
            'active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === User::ROLE_ADMIN) {
            if ($user->created_by !== $currentUser->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
