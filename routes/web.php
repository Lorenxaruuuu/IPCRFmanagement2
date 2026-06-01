<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Admin\IpcrfController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PerformanceController;
use App\Http\Admin\NoticeController;
use App\Http\Admin\FormController;
use App\Http\Controllers\GoogleDriveAuthController;
use App\Http\Controllers\SuperadminController;

Route::get('/admins', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/notifications2', function () {
    return view('notifications_page');
})->name('notifications');


Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
    Route::get('/performance/{id}/view', [PerformanceController::class, 'show'])->name('performance.show');
    Route::get('/performance/download-report', [PerformanceController::class, 'downloadReport'])->name('performance.download');Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
// download a single form; use numeric id since we have no database
Route::get('/forms/download/{id}', [FormController::class, 'download'])->name('forms.download');
Route::get('/', function () {
    if (session()->has('user') || session()->has('employee_id')) {
        $user = session('user');
        if ($user && isset($user['role'])) {
            if ($user['role'] === 'superadmin') {
                return redirect()->route('superadmin.dashboard');
            } elseif ($user['role'] === 'admin') {
                return redirect('/admins');
            } elseif ($user['role'] === 'encoder') {
                return redirect('/encoder');
            }
        }
        return redirect()->route('userDashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/home', function () {
    return view('userDashboard', ['activeTab' => 'home']);
})->name('userDashboard');

Route::get('/settings', function () {
    return view('userDashboard', ['activeTab' => 'settings']);
})->name('settings');

Route::get('/encoder', [IpcrfController::class, 'index'])->name('dashboards');
Route::get('/list', [IpcrfController::class, 'showList'])->name('ipcrf.list');
Route::get('/upload', [IpcrfController::class, 'create'])->name('upload.create');
Route::post('/upload', [IpcrfController::class, 'store'])->name('upload.store');

Route::prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [IpcrfController::class, 'dashboard'])->name('dashboard');
    
    // Upload - Single page, no steps
    Route::get('/upload', [IpcrfController::class, 'uploadForm'])->name('upload');
    Route::post('/upload', [IpcrfController::class, 'store2'])->name('upload.store');
    
    // Records
    Route::get('/records', [IpcrfController::class, 'records'])->name('records');
    Route::get('/records/{id}/download', [IpcrfController::class, 'download'])->name('records.download');
    Route::delete('/records/{id}', [IpcrfController::class, 'destroy'])->name('records.destroy');
    
    // API for cascading dropdowns (controller-based)
    Route::get('/api/provinces/{province}/municipalities', [IpcrfController::class, 'getMunicipalities']);
    Route::get('/api/municipalities/{municipality}/schools', [IpcrfController::class, 'getSchools']);
    
    // Notices
    Route::get('/notices', [NoticeController::class, 'index'])->name('notices');
    Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
    Route::delete('/notices/{id}', [NoticeController::class, 'destroy'])->name('notices.destroy');
    
    // Forms
    Route::get('/forms', [FormController::class, 'index'])->name('forms');
    Route::post('/forms', [FormController::class, 'store'])->name('forms.store');

    Route::get('/forms/{id}/download', [FormController::class, 'download2'])->name('forms.download');
    Route::delete('/forms/{id}', [FormController::class, 'destroy'])->name('forms.destroy');
    
    // Google Drive Authorization
    Route::get('/settings/google-drive/authorize', [GoogleDriveAuthController::class, 'authorize'])->name('gdrive.authorize');
});

// Google Drive OAuth Callback (outside admin group)
Route::get('/auth/google/callback', [GoogleDriveAuthController::class, 'callback'])->name('gdrive.callback');

Route::post('/notifications/mark-all-read', function () {
    if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
        DB::table('notifications')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
    session(['notices_read_at' => now()]);
    return back()->with('success', 'All announcements marked as read.');
})->name('notifications.markAllAsRead');

Route::get('/notifications', function () {
    return view('notifications_page');
})->name('notifications.index');

Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.changePassword');

Route::post('/register.php', function () {
    $data = request()->json()->all();
    
    if (empty($data['lastname']) || empty($data['firstname']) || empty($data['email']) || 
        empty($data['password']) || empty($data['role'])) {
        return response()->json(['success' => false, 'message' => 'All fields are required']);
    }
    
    if ($data['password'] !== $data['password_confirmation']) {
        return response()->json(['success' => false, 'message' => 'Passwords do not match']);
    }
    
    $email = $data['email'];
    if (!str_contains($email, '@')) {
        $email .= '@dswd.gov.ph';
    }
    
    $exists = DB::table('users')->where('email', $email)->exists();
    if ($exists) {
        return response()->json(['success' => false, 'message' => 'Email already exists']);
    }
    
    $employee_id = explode('@', $email)[0];
    
    DB::table('users')->insert([
        'lastname' => $data['lastname'],
        'firstname' => $data['firstname'],
        'name' => $data['firstname'] . ' ' . $data['lastname'],
        'employee_id' => $employee_id,
        'email' => $email,
        'password' => Hash::make($data['password']),
        'role' => $data['role'],
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    return response()->json(['success' => true, 'message' => 'Registration successful! Your account is pending superadmin approval. You can log in once approved.']);
});

Route::post('/login.php', function () {
    $data = request()->json()->all();
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        return response()->json(['success' => false, 'message' => 'Please enter both Email and Password']);
    }
    
    $raw_input = $data['email'] ?? '';
    if (!str_contains($email, '@')) {
        $email .= '@dswd.gov.ph';
    }
    
    $user = DB::table('users')
        ->where('email', $email)
        ->orWhere('email', $raw_input)
        ->orWhere('employee_id', $email)
        ->orWhere('employee_id', $raw_input)
        ->first();
    
    if (!$user || !Hash::check($password, $user->password)) {
        return response()->json(['success' => false, 'message' => 'Invalid Email/ID or Password']);
    }
    
    if ($user->role !== 'superadmin' && !$user->approved) {
        return response()->json(['success' => false, 'message' => 'Your account is pending superadmin approval.']);
    }
    
    Session::put('user', [
        'employee_id' => $user->employee_id,
        'name' => $user->name,
        'role' => $user->role,
        'email' => $user->email,
        'firstname' => $user->firstname,
        'lastname' => $user->lastname
    ]);
    
    $redirectUrl = '/home';
    switch ($user->role) {
        case 'encoder':
            $redirectUrl = '/encoder';
            break;
        case 'admin':
            $redirectUrl = '/admins';
            break;
        case 'superadmin':
            $redirectUrl = '/superadmin/dashboard2';
            break;
        default:
            $redirectUrl = '/home';
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirectUrl,
        'role' => $user->role,
        'user' => [
            'id' => $user->id,
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'role' => $user->role
        ]
    ]);
});


// lightweight API endpoints (no controller)

// Superadmin Routes
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard2', [SuperadminController::class, 'dashboard'])->name('dashboard');
    Route::post('/users/{id}/approve', [SuperadminController::class, 'approve'])->name('users.approve');
    Route::delete('/users/{id}/reject', [SuperadminController::class, 'reject'])->name('users.reject');
    Route::post('/admin/create', [SuperadminController::class, 'createAdmin'])->name('admin.create');
});
