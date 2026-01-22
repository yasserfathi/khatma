<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Group::query();

            // Cast role to int to be safe
            if ($user && (int) $user->role === \App\Models\User::ROLE_ADMIN) {
                $query->where('created_by', $user->id);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            return $query->get();
        } catch (\Exception $e) {
            file_put_contents(public_path('debug.txt'), $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'active' => 'boolean',
            'type' => 'nullable|string|in:tilawa,zikr',
        ]);

        if (!isset($validated['type'])) {
            $validated['type'] = 'tilawa';
        }

        if ($request->user()) {
            $validated['created_by'] = $request->user()->id;
        }

        $group = Group::create($validated);

        return response()->json($group, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Group $group)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            if ($group->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        return $group;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            if ($group->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:groups,name,' . $group->id,
            'active' => 'boolean',
            'type' => 'nullable|string|in:tilawa,zikr',
        ]);

        $group->update($validated);

        return $group;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Group $group)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            if ($group->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $group->delete();

        return response()->json(null, 204);
    }
}
