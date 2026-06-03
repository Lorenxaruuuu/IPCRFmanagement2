<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        $user = Session::get('user');
        if ($user && isset($user['role']) && isset($user['id'])) {
            // Only redirect if we have a full valid session
            if ($user['role'] === 'superadmin') {
                return redirect('/superadmin/dashboard2');
            } elseif ($user['role'] === 'admin') {
                $admin = User::find($user['id'] ?? 0);
                if ($admin && $admin->adminPositionType() === 'poo') {
                    return redirect()->route('admin.poo.dashboard');
                }
                return redirect('/admins');
            } elseif ($user['role'] === 'encoder') {
                return redirect('/encoder');
            } else {
                return redirect()->route('userDashboard');
            }
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $email = $request->email;
        $raw_input = $request->email;
        if (!str_contains($email, '@')) {
            $email .= '@dswd.gov.ph';
        }

        // Check if user exists in database
        $user = User::where('email', $email)
            ->orWhere('email', $raw_input)
            ->orWhere('employee_id', $email)
            ->orWhere('employee_id', $raw_input)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->role !== 'superadmin' && !$user->approved) {
                return back()->withErrors(['email' => 'Your account is pending superadmin approval.']);
            }

            // Successful authentication
            Session::put('user', [
                'id' => $user->id,
                'employee_id' => $user->employee_id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'position' => $user->position
            ]);

            if ($user->role === 'superadmin') {
                return redirect('/superadmin/dashboard2');
            } elseif ($user->role === 'admin') {
                if ($user->adminPositionType() === 'poo') {
                    return redirect()->route('admin.poo.dashboard');
                }
                return redirect('/admins');
            } elseif ($user->role === 'encoder') {
                return redirect('/encoder');
            }

            return redirect()->route('userDashboard');
        }

        // Failed authentication
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function showRegisterForm()
    {
        if (Session::has('user')) {
            return view('userDashboard');
        }
        return view('auth.register');
    }

  public function register(Request $request)
{
    $request->validate([
        'lastname' => 'required|string|max:255',
        'firstname' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:8',
        'role' => 'required|in:admin,staff,encoder,viewer',
    ]);

    $email = $request->email;
    if (!str_contains($email, '@')) {
        $email .= '@dswd.gov.ph';
    }

    $employee_id = explode('@', $email)[0];

    $user = User::create([
        'firstname' => $request->firstname,
        'lastname' => $request->lastname,
        'name' => $request->firstname . ' ' . $request->lastname,
        'employee_id' => $employee_id,
        'email' => $email,
        'password' => bcrypt($request->password),
        'role' => $request->role,
    ]);

    return redirect()->route('login')->with('success', 'Registration successful! Your account is pending superadmin approval.');
}
public function store(Request $request)
{
    $validated = $request->validate([
        'lastname' => 'required|string|max:100',
        'firstname' => 'required|string|max:100',
        'employee_id' => 'required|string|unique:users',
        'password' => 'required|min:8|confirmed',
        'role' => 'required|in:admin,staff,encoder,viewer'
    ]);

    User::create([
        'lastname' => $validated['lastname'],
        'firstname' => $validated['firstname'],
        'employee_id' => $validated['employee_id'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role']
    ]);

    return redirect()->route('login')->with('success', 'Registration pending admin approval');
}

    public function updateProfile(Request $request)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionUser = session('user');
        if (!$sessionUser && isset($_SESSION['employee_id'])) {
            $sessionUser = [
                'employee_id' => $_SESSION['employee_id'],
                'name' => ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''),
                'role' => $_SESSION['role'] ?? 'staff',
                'email' => $_SESSION['email'] ?? '',
                'firstname' => $_SESSION['firstname'] ?? '',
                'lastname' => $_SESSION['lastname'] ?? '',
            ];
            session(['user' => $sessionUser]);
        }

        if (!$sessionUser) {
            return redirect()->route('login');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'birthday' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'emails' => 'nullable|array',
            'emails.*' => 'required|email',
        ]);

        $primaryEmail = $request->email;
        if ($request->has('emails') && is_array($request->emails) && count($request->emails) > 0) {
            $primaryEmail = $request->emails[0];
        }

        $user = User::where('employee_id', $sessionUser['employee_id'])->first();
        if ($user) {
            // Split full name into firstname and lastname
            $parts = explode(' ', $request->full_name, 2);
            $firstname = $parts[0] ?? '';
            $lastname = $parts[1] ?? '';

            if ($user->profile_edited) {
                // If already edited, block changes to locked fields
                $incomingBirthday = $request->birthday;
                $dbBirthday = $user->birthday;
                try {
                    if ($incomingBirthday && $dbBirthday) {
                        $incomingTs = strtotime($incomingBirthday);
                        $dbTs = strtotime($dbBirthday);
                        if ($incomingTs !== false && $dbTs !== false && $incomingTs === $dbTs) {
                            $incomingBirthday = $dbBirthday;
                        }
                    }
                } catch (\Exception $e) {}

                if ($firstname !== $user->firstname || 
                    $lastname !== $user->lastname || 
                    ($incomingBirthday && $incomingBirthday !== $dbBirthday) || 
                    $request->gender !== $user->gender || 
                    $request->address !== $user->address || 
                    $request->region !== $user->province) {
                    return back()->withErrors(['profile_error' => 'You have already edited your personal information once and it cannot be modified again.']);
                }

                // If no locked fields are changed, only update email
                $user->update([
                    'email' => $primaryEmail,
                ]);
            } else {
                // First-time edit: format birthday to human-readable January 15, 1990
                $formattedBirthday = $request->birthday;
                if ($request->filled('birthday')) {
                    try {
                        $formattedBirthday = date('F j, Y', strtotime($request->birthday));
                    } catch (\Exception $e) {}
                }

                $user->update([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'name' => $request->full_name,
                    'email' => $primaryEmail,
                    'birthday' => $formattedBirthday,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'province' => $request->region,
                    'profile_edited' => 1,
                ]);
            }

            // Sync with session
            Session::put('user', [
                'id' => $user->id,
                'employee_id' => $user->employee_id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
            ]);

            // Sync with native PHP session
            $_SESSION['firstname'] = $user->firstname;
            $_SESSION['lastname'] = $user->lastname;
            $_SESSION['email'] = $user->email;
        }

        // Store extra profile fields and preferences in session
        session([
            'profile_birthday' => $request->birthday,
            'profile_gender' => $request->gender,
            'profile_address' => $request->address,
            'profile_region' => $request->region,
            'pref_email_notifications' => $request->has('email_notifications'),
            'pref_push_notifications' => $request->has('push_notifications'),
            'profile_emails' => $request->emails ?? [$primaryEmail],
        ]);

        return back()->with('success', 'Profile and preferences updated successfully!');
    }

    public function changePassword(Request $request)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionUser = session('user');
        if (!$sessionUser && isset($_SESSION['employee_id'])) {
            $sessionUser = [
                'employee_id' => $_SESSION['employee_id'],
                'name' => ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''),
                'role' => $_SESSION['role'] ?? 'staff',
                'email' => $_SESSION['email'] ?? '',
                'firstname' => $_SESSION['firstname'] ?? '',
                'lastname' => $_SESSION['lastname'] ?? '',
            ];
            session(['user' => $sessionUser]);
        }

        if (!$sessionUser) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        $user = User::where('employee_id', $sessionUser['employee_id'])->first();
        if (!$user) {
            return back()->withErrors(['current_password' => 'User not found.']);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function requestRoleChange(Request $request)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionUser = session('user');
        if (!$sessionUser && isset($_SESSION['employee_id'])) {
            $sessionUser = [
                'employee_id' => $_SESSION['employee_id'],
            ];
        }

        if (!$sessionUser) {
            return redirect()->route('login');
        }

        $request->validate([
            'requested_role' => 'required|string|max:255',
        ]);

        $user = User::where('employee_id', $sessionUser['employee_id'])->first();
        if (!$user) {
            return back()->withErrors(['requested_role' => 'User not found.']);
        }

        if ($user->role === $request->requested_role) {
            return back()->withErrors(['requested_role' => 'You already have this role.']);
        }

        $user->update([
            'requested_role' => $request->requested_role
        ]);

        return back()->with('success', 'Role change request submitted successfully. It is pending admin approval.');
    }

    public function logout()
    {
        Session::forget('user');
        return redirect()->route('login');
    }
}