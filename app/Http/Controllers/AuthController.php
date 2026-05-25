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
        if (Session::has('employee_id')) {
            return view('userDashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'password' => 'required',
        ]);

        // Check if user exists in database
        $user = User::where('employee_id', $request->employee_id)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Successful authentication
            Session::put('user', [
                'employee_id' => $user->employee_id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname
            ]);

            return redirect()->route('userDashboard');
        }

        // Failed authentication
        return back()->withErrors(['employee_id' => 'Invalid credentials']);
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
        'employee_id' => 'required|unique:users,employee_id',
        'password' => 'required|confirmed|min:8',
        'role' => 'required|in:admin,staff,encoder,viewer',
    ]);

    $user = User::create([
        'firstname' => $request->firstname,
        'lastname' => $request->lastname,
        'name' => $request->firstname . ' ' . $request->lastname,
        'employee_id' => $request->employee_id,
        'email' => $request->employee_id . '@dswd.gov.ph',
        'password' => bcrypt($request->password),
        'role' => $request->role,
    ]);

    Session::put('user', [
        'employee_id' => $user->employee_id,
        'name' => $user->name,
        'role' => $user->role
    ]);

    return redirect()->back()->with('success', 'Registration successful! Welcome, ' . $user->name . '. You will be redirected shortly.');
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
                    $request->region !== $user->region) {
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
                    'region' => $request->region,
                    'profile_edited' => 1,
                ]);
            }

            // Sync with session
            Session::put('user', [
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

    public function logout()
    {
        Session::forget('user');
        return redirect()->route('login');
    }
}