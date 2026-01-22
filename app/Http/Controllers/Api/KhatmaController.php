<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Khatma;
use Illuminate\Http\Request;

class KhatmaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            return Khatma::with('group')
                ->where('created_by', $user->id)
                ->latest()
                ->get();
        }
        return Khatma::with('group')->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'khatma_no' => 'nullable|string|max:50',
            'people_group_no' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'hijri_date' => 'nullable|string',
            'juz_count' => 'nullable|integer|min:1',
        ]);

        if ($request->user() && $request->user()->role === \App\Models\User::ROLE_ADMIN) {
            $group = \App\Models\Group::find($validated['group_id']);
            if ($group->created_by !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        if ($request->user()) {
            $validated['created_by'] = $request->user()->id;
        }

        $khatma = Khatma::create($validated);

        return response()->json($khatma->load('group'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Khatma $khatma)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            if ($khatma->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        return $khatma->load('group');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Khatma $khatma)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            if ($khatma->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $validated = $request->validate([
            'group_id' => 'sometimes|required|exists:groups,id',
            'khatma_no' => 'nullable|string|max:50',
            'people_group_no' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'hijri_date' => 'nullable|string',
            'juz_count' => 'nullable|integer|min:1',
        ]);

        // Also check if moving to a new group, that the new group is owned by admin
        if (isset($validated['group_id']) && $user && $user->role === \App\Models\User::ROLE_ADMIN) {
            $group = \App\Models\Group::find($validated['group_id']);
            if ($group->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $khatma->update($validated);

        return $khatma->load('group');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Khatma $khatma)
    {
        $user = $request->user();
        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            if ($khatma->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $khatma->delete();

        return response()->json(null, 204);
    }
}
