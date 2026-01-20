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
    public function index()
    {
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
        ]);

        $khatma = Khatma::create($validated);

        return response()->json($khatma->load('group'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Khatma $khatma)
    {
        return $khatma->load('group');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Khatma $khatma)
    {
        $validated = $request->validate([
            'group_id' => 'sometimes|required|exists:groups,id',
            'khatma_no' => 'nullable|string|max:50',
            'people_group_no' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $khatma->update($validated);

        return $khatma->load('group');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Khatma $khatma)
    {
        $khatma->delete();

        return response()->json(null, 204);
    }
}
