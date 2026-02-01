<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZikrGroupParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $participant = ZikrGroupParticipant::where('group_id', $validated['group_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($participant) {
            $participant->update(['participant_no' => $validated['participant_no']]);
        } else {
            $participant = ZikrGroupParticipant::create(array_merge($validated, ['created_by' => Auth::id()]));
        }

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
            $participant = ZikrGroupParticipant::where('group_id', $validated['group_id'])
                ->where('user_id', $p['user_id'])
                ->first();

            if ($participant) {
                $participant->update(['participant_no' => $p['participant_no'] ?? null]);
            } else {
                ZikrGroupParticipant::create([
                    'group_id' => $validated['group_id'],
                    'user_id' => $p['user_id'],
                    'participant_no' => $p['participant_no'] ?? null,
                    'created_by' => Auth::id()
                ]);
            }
        }

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }
}
