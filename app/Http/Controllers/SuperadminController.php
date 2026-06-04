<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Province;

class SuperadminController extends Controller
{
    private function checkAccess()
    {
        // Start native PHP session if not started (public/login.php uses $_SESSION)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = session('user');

        // If Laravel session not set, check native PHP session and sync
        if (!$user && isset($_SESSION['employee_id'])) {
            $user = [
                'employee_id' => $_SESSION['employee_id'],
                'name' => ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''),
                'role' => $_SESSION['role'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'firstname' => $_SESSION['firstname'] ?? '',
                'lastname' => $_SESSION['lastname'] ?? '',
            ];
            session(['user' => $user]);
        }

        if (!$user || $user['role'] !== 'superadmin') {
            return false;
        }
        return true;
    }

    public function dashboard()
    {
        if (!$this->checkAccess()) {
            return redirect()->route('login');
        }

        $pendingUsers = User::where('approved', false)
            ->where('role', '!=', 'superadmin')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeUsers = User::where('approved', true)
            ->where('role', '!=', 'superadmin')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'pending' => $pendingUsers->count(),
            'active' => $activeUsers->count(),
            'admins' => User::where('role', 'admin')->count(),
            'encoders' => User::where('role', 'encoder')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'viewers' => User::where('role', 'viewer')->count(),
        ];

        $pendingRoleChanges = User::whereNotNull('requested_position_id')
            ->with(['position', 'requestedPosition'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $provinces = Province::orderBy('name')->pluck('name');

        return view('superadmin.dashboard', compact('pendingUsers', 'activeUsers', 'stats', 'pendingRoleChanges', 'provinces'));
    }

    public function approve($id)
    {
        if (!$this->checkAccess()) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($id);
        $user->update(['approved' => true]);

        return back()->with('success', 'User ' . $user->name . ' has been successfully approved.');
    }

    public function reject($id)
    {
        if (!$this->checkAccess()) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        return back()->with('success', 'Registration request for ' . $name . ' has been rejected.');
    }

    public function approveRoleChange($id)
    {
        if (!$this->checkAccess()) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($id);
        if ($user->requested_position_id) {
            $newPositionId = $user->requested_position_id;
            $newPositionName = $user->requestedPosition->name ?? 'Unknown';
            $user->update([
                'position_id' => $newPositionId,
                'requested_position_id' => null
            ]);
            return back()->with('success', 'Position change to ' . $newPositionName . ' for ' . $user->name . ' has been approved.');
        }

        return back()->with('error', 'No pending position change request for this user.');
    }

    public function rejectRoleChange($id)
    {
        if (!$this->checkAccess()) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($id);
        if ($user->requested_position_id) {
            $user->update(['requested_position_id' => null]);
            return back()->with('success', 'Position change request for ' . $user->name . ' has been rejected.');
        }

        return back()->with('error', 'No pending position change request for this user.');
    }

    public function createAdmin(Request $request)
    {
        if (!$this->checkAccess()) {
            return redirect()->route('login');
        }

        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:users,employee_id',
            'password' => 'required|string|min:8|confirmed',
            'position' => 'required|in:rpmo,poo,rpmo_poo,none',
            'assigned_province' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'name' => $request->firstname . ' ' . $request->lastname,
            'employee_id' => $request->employee_id,
            'email' => $request->employee_id . '@dswd.gov.ph',
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'position' => $request->position,
            'assigned_province' => $request->assigned_province,
            'approved' => true,
        ]);

        return back()->with('success', 'Administrator account for ' . $user->name . ' was created successfully.');
    }
}
