<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZikrKhatmaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZikrKhatmaAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $khatmaId = $request->khatma_id;
        $query = ZikrKhatmaAssignment::with(['user', 'creator']);
        if ($khatmaId) {
            $query->where('khatma_id', $khatmaId);
        }
        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'khatma_id' => 'required|exists:khatmas,id',
            'user_id' => 'required|exists:users,id',
            'zikr_count' => 'required|integer|min:0'
        ]);

        $validated['created_by'] = Auth::id();
        $assignment = ZikrKhatmaAssignment::create($validated);
        return response()->json($assignment->load('user'), 201);
    }

    public function update(Request $request, ZikrKhatmaAssignment $zikrKhatmaAssignment)
    {
        $validated = $request->validate([
            'zikr_count' => 'sometimes|required|integer|min:0'
        ]);

        $zikrKhatmaAssignment->update($validated);
        return $zikrKhatmaAssignment->load('user');
    }

    public function destroy(ZikrKhatmaAssignment $zikrKhatmaAssignment)
    {
        $zikrKhatmaAssignment->delete();
        return response()->json(null, 204);
    }
}
