<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\KhatmaAssignment;

class KhatmaAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = KhatmaAssignment::with(['user', 'khatma']);

        if ($request->has('khatma_id')) {
            $query->where('khatma_id', $request->khatma_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        try {
            $khatmaId = $request->input('khatma_id');
            $khatma = \App\Models\Khatma::find($khatmaId);

            if (!$khatma) {
                return response()->json(['message' => 'Khatma not found'], 404);
            }

            $maxJuz = $khatma->juz_count ?? 30;

            $validated = $request->validate([
                'khatma_id' => 'required|exists:khatmas,id',
                'user_id' => 'required|exists:users,id',
                'parts' => 'required|array',
                'parts.*' => 'integer|min:1|max:' . $maxJuz,
                'read' => 'boolean'
            ]);

            // Check if there is already an assignment for this user in this khatma
            $existingUserAssignment = KhatmaAssignment::where('khatma_id', $validated['khatma_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            // Check for overlap with OTHER users
            $otherAssignmentsQuery = KhatmaAssignment::where('khatma_id', $validated['khatma_id']);

            if ($existingUserAssignment) {
                $otherAssignmentsQuery->where('id', '!=', $existingUserAssignment->id);
            }

            $otherAssignments = $otherAssignmentsQuery->get();

            $requestedParts = collect($validated['parts']);

            foreach ($otherAssignments as $existing) {
                if (empty($existing->parts))
                    continue;
                // Check intersection
                $existingParts = collect($existing->parts);
                $overlap = $requestedParts->intersect($existingParts);

                if ($overlap->isNotEmpty()) {
                    return response()->json([
                        'message' => 'Some parts are already assigned: ' . $overlap->implode(', ')
                    ], 422);
                }
            }

            if ($existingUserAssignment) {
                // Merge parts
                $currentParts = $existingUserAssignment->parts ?? [];
                $newParts = array_values(array_unique(array_merge($currentParts, $validated['parts'])));
                sort($newParts);

                // If the user specifically set read=true in this request, maybe we should honor it?
                // Or if they added parts, usually it implies 'unread' unless checked. 
                // We'll update the read status to whatever came in the request or default to false/false?
                // The request says "read" boolean. If checkbox is checked -> true. Unchecked -> false.
                // If I merge new parts and the box was unchecked, surely the whole thing isn't read yet?
                // So updating 'read' to the request value is safe logic (Checkbox is the truth).

                $existingUserAssignment->update([
                    'parts' => $newParts,
                    'read' => $validated['read'] ?? false
                ]);
                $assignment = $existingUserAssignment;
            } else {
                $assignment = KhatmaAssignment::create([
                    'khatma_id' => $validated['khatma_id'],
                    'user_id' => $validated['user_id'],
                    'parts' => $validated['parts'],
                    'read' => $validated['read'] ?? false
                ]);
            }

            return response()->json($assignment->load('user'), 201); // 201 Created or 200 OK, keeping 201 for simplicity or dynamic
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, KhatmaAssignment $khatma_assignment) // Implicit binding might fail if route key is not matching 'assignment', relying on id
    {
        $assignment = $khatma_assignment;
        // Fetch khatma to check max juz
        $khatma = $assignment->khatma;
        $maxJuz = $khatma ? ($khatma->juz_count ?? 30) : 30;

        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'parts' => 'array',
            'parts.*' => 'integer|min:1|max:' . $maxJuz,
            'read' => 'boolean'
        ]);

        if (isset($validated['parts'])) {
            // Check for overlap, excluding current assignment
            $existingAssignments = KhatmaAssignment::where('khatma_id', $assignment->khatma_id)
                ->where('id', '!=', $assignment->id)
                ->get();

            $requestedParts = collect($validated['parts']);

            foreach ($existingAssignments as $existing) {
                if (empty($existing->parts))
                    continue;
                $existingParts = collect($existing->parts);
                $overlap = $requestedParts->intersect($existingParts);

                if ($overlap->isNotEmpty()) {
                    return response()->json([
                        'message' => 'Some parts are already assigned: ' . $overlap->implode(', ')
                    ], 422);
                }
            }
        }

        $assignment->update($validated);

        return $assignment->load('user');
    }

    public function destroy(KhatmaAssignment $khatma_assignment)
    {
        $khatma_assignment->delete();
        return response()->json(null, 204);
    }
}
