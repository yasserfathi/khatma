<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZikrGroupParticipant;
use Illuminate\Http\Request;

class ZikrGroupParticipantController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->group_id;
        if (!$groupId) {
            return response()->json(['message' => 'Group ID is required'], 422);
        }
        return ZikrGroupParticipant::where('group_id', $groupId)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'user_id' => 'required|exists:users,id',
            'participant_no' => 'nullable|string',
        ]);

        $participant = ZikrGroupParticipant::updateOrCreate(
            ['group_id' => $validated['group_id'], 'user_id' => $validated['user_id']],
            ['participant_no' => $validated['participant_no']]
        );

        return response()->json($participant);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'participants' => 'required|array',
            'participants.*.user_id' => 'required|exists:users,id',
            'participants.*.participant_no' => 'nullable|string',
        ]);

        foreach ($validated['participants'] as $p) {
            ZikrGroupParticipant::updateOrCreate(
                ['group_id' => $validated['group_id'], 'user_id' => $p['user_id']],
                ['participant_no' => $p['participant_no'] ?? null]
            );
        }

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }
}
