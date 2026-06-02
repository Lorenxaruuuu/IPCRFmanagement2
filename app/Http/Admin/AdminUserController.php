<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('position')
            ->whereNotIn('role', ['superadmin', 'admin'])
            ->latest();

        if ($request->filled('search')) {
            $query->where(fn($q) => $q
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('employee_id', 'like', '%' . $request->search . '%')
            );
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        $users = $query->paginate(20);

        return response()->json([
            'users'      => $users->items(),
            'pagination' => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $user = User::with(['position', 'submissions.template'])->findOrFail($id);
        return response()->json(['user' => $user]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'firstname'   => 'required|string|max:255',
            'lastname'    => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $id,
            'position_id' => 'nullable|exists:positions,id',
            'department'  => 'nullable|string|max:255',
            'office'      => 'nullable|string|max:255',
            'approved'    => 'boolean',
        ]);

        $user->update([
            'firstname'   => $request->firstname,
            'lastname'    => $request->lastname,
            'name'        => $request->firstname . ' ' . $request->lastname,
            'email'       => $request->email,
            'position_id' => $request->position_id,
            'department'  => $request->department,
            'office'      => $request->office,
            'approved'    => $request->approved ?? $user->approved,
        ]);

        return response()->json(['success' => true, 'user' => $user->fresh('position')]);
    }

    public function approve(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['approved' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'User approved.']);
        }
        return redirect()->route('admin.dashboard')->with('success', "User {$user->name} has been approved.");
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.dashboard')->with('success', 'User deleted.');
    }
}
