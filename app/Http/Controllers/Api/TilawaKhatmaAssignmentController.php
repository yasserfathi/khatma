<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TilawaKhatmaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TilawaKhatmaAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $khatmaId = $request->khatma_id;
        $query = TilawaKhatmaAssignment::with('user');
        if ($khatmaId) {
            $query->where('khatma_id', $khatmaId);
        }

        $user = Auth::user();
        if ($user && $user->role === User::ROLE_ADMIN) {
            $query->where('created_by', $user->id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'khatma_id' => 'required|exists:khatmas,id',
            'user_id' => 'required|exists:users,id',
            'parts' => 'required|array',
            'read' => 'boolean'
        ]);

        $assignment = TilawaKhatmaAssignment::create(array_merge($validated, ['created_by' => Auth::id()]));
        return response()->json($assignment->load('user'), 201);
    }

    public function update(Request $request, TilawaKhatmaAssignment $tilawaKhatmaAssignment)
    {
        $validated = $request->validate([
            'parts' => 'sometimes|required|array',
            'read' => 'sometimes|boolean'
        ]);

        $tilawaKhatmaAssignment->update($validated);
        return $tilawaKhatmaAssignment->load('user');
    }

    public function destroy(TilawaKhatmaAssignment $tilawaKhatmaAssignment)
    {
        $tilawaKhatmaAssignment->delete();
        return response()->json(null, 204);
    }
}
