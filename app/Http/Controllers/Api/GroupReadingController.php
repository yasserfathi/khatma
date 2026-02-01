<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GroupReading;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupReadingController extends Controller
{
    public function index()
    {
        $query = GroupReading::with('creator')->latest();
        $user = Auth::user();

        if ($user && $user->role === User::ROLE_ADMIN) {
            $query->where('created_by', $user->id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_no' => 'required',
            'names' => 'required',
        ]);

        $validated['created_by'] = Auth::id();

        return GroupReading::create($validated);
    }

    public function update(Request $request, GroupReading $groupReading)
    {
        $validated = $request->validate([
            'group_no' => 'required',
            'names' => 'required',
        ]);

        $groupReading->update($validated);

        return $groupReading;
    }

    public function destroy(GroupReading $groupReading)
    {
        $groupReading->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
