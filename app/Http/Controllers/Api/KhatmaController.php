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
        $type = $request->type; // 'tilawa' or 'zikr'

        $query = Khatma::with(['group', 'groupReading']);

        if ($type) {
            $query->whereHas('group', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            $khatmas = $query->where('created_by', $user->id)
                ->latest()
                ->get();
        } else {
            $khatmas = $query->latest()->get();
        }

        // Calculate progress
        foreach ($khatmas as $khatma) {
            if ($khatma->group->type === 'tilawa') {
                $assignments = $khatma->tilawaAssignments;
                $totalReaders = $assignments->count();
                $finishedReaders = $assignments->where('read', true)->count();

                $khatma->progress = [
                    'finished' => $finishedReaders,
                    'total' => $totalReaders,
                    'percentage' => $totalReaders > 0 ? round(($finishedReaders / $totalReaders) * 100) : 0
                ];
            } else {
                // For Zikr, count how many participants have entered a count
                $assignments = $khatma->zikrAssignments;
                $totalParticipants = $assignments->count();
                $finishedParticipants = $assignments->where('zikr_count', '>', 0)->count();

                $khatma->progress = [
                    'finished' => $finishedParticipants,
                    'total' => $totalParticipants ?: 1,
                    'percentage' => $totalParticipants > 0 ? round(($finishedParticipants / $totalParticipants) * 100) : 0
                ];
            }
        }

        return $khatmas;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'group_reading_id' => 'nullable|exists:group_readings,id',
            'khatma_no' => 'nullable|string|max:50',
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

        return response()->json($khatma->load(['group', 'groupReading']), 201);
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
        return $khatma->load(['group', 'groupReading']);
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
            'group_reading_id' => 'nullable|exists:group_readings,id',
            'khatma_no' => 'nullable|string|max:50',
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

        return $khatma->load(['group', 'groupReading']);
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
