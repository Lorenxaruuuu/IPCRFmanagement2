<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AdminPositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('users')->latest()->get();
        return response()->json(['positions' => $positions]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string',
        ]);

        $position = Position::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        AuditService::log('position_created', null, 'Position', $position->id, ['name' => $position->name]);

        return response()->json(['success' => true, 'position' => $position]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name,' . $id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $position = Position::findOrFail($id);
        $position->update($request->only('name', 'description', 'is_active'));

        return response()->json(['success' => true, 'position' => $position]);
    }

    public function destroy(int $id)
    {
        $position = Position::findOrFail($id);
        $position->delete();
        AuditService::log('position_deleted', null, 'Position', $id);
        return response()->json(['success' => true]);
    }
}
