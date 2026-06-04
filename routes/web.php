<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Admin\IpcrfController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Admin\NoticeController;
use App\Http\Admin\FormController;
use App\Http\Admin\AdminTemplateController;
use App\Http\Admin\AdminSubmissionController;
use App\Http\Admin\AdminPositionController;
use App\Http\Admin\AdminUserController;
use App\Http\Controllers\GoogleDriveAuthController;
use App\Http\Controllers\SuperadminController;

Route::get('/admins', function () {
    $sessionUser = session('user');
    if ($sessionUser && ($sessionUser['role'] ?? '') === 'admin') {
        $admin = \App\Models\User::find($sessionUser['id'] ?? 0);
        if ($admin && \App\Support\AdminPosition::isPooOnly($admin->adminPositionType())) {
            return redirect()->route('admin.poo.dashboard');
        }
    }
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
    if (session()->has('user')) {
        $user = session('user');
        if ($user && isset($user['role']) && isset($user['id'])) {
            if ($user['role'] === 'superadmin') {
                return redirect('/superadmin/dashboard2');
            } elseif ($user['role'] === 'admin') {
                $admin = \App\Models\User::find($user['id'] ?? 0);
                if ($admin && \App\Support\AdminPosition::isPooOnly($admin->adminPositionType())) {
                    return redirect()->route('admin.poo.dashboard');
                }
                return redirect('/admins');
            } elseif ($user['role'] === 'encoder') {
                return redirect('/encoder');
            }
            return redirect()->route('userDashboard');
        }
    }
    // Show landing page for guests
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/home', function () {
    return view('userDashboard', ['activeTab' => 'ipcrf']);
})->name('userDashboard');

Route::get('/settings', function () {
    return view('userDashboard', ['activeTab' => 'settings']);
})->name('settings');

Route::get('/encoder', [IpcrfController::class, 'index'])->name('dashboards');
Route::get('/list', [IpcrfController::class, 'showList'])->name('ipcrf.list');
Route::get('/upload', [IpcrfController::class, 'create'])->name('upload.create');
Route::post('/upload', [IpcrfController::class, 'store'])->name('upload.store');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::post('/submissions/{id}/save-answers', [\App\Http\Admin\AdminSubmissionController::class, 'saveAnswers'])->name('submissions.save-answers');

    // POO Admin only (provincial quality control)
    Route::middleware('poo.admin')->prefix('poo')->group(function () {
        Route::get('/dashboard', [\App\Http\Admin\PooAdminController::class, 'dashboard'])->name('poo.dashboard');
        Route::get('/queue', [\App\Http\Admin\PooAdminController::class, 'provincialQueue'])->name('poo.queue');
        Route::get('/staff', [\App\Http\Admin\PooAdminController::class, 'staffDirectory'])->name('poo.staff');
        Route::get('/archives', [\App\Http\Admin\PooAdminController::class, 'archives'])->name('poo.archives');
        Route::get('/submissions/{id}/inspect', [\App\Http\Admin\PooAdminController::class, 'inspectSubmission'])->name('poo.submissions.inspect');
        Route::get('/submissions/{id}/download', [\App\Http\Admin\PooAdminController::class, 'download'])->name('poo.submissions.download');
        Route::post('/submissions/{id}/return', [\App\Http\Admin\PooAdminController::class, 'returnForCorrection'])->name('poo.submissions.return');
        Route::post('/submissions/{id}/approve', [\App\Http\Admin\PooAdminController::class, 'approve'])->name('poo.submissions.approve');
    });

    // RPMO Admin only (regional management)
    Route::middleware('rpmo.admin')->group(function () {
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

    // ─── IPCRF Template Management ───────────────────────────────────────────
    Route::get('/templates/all',              [AdminTemplateController::class, 'getAll'])->name('templates.all');
    Route::post('/templates/upload',          [AdminTemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{id}/builder',     [AdminTemplateController::class, 'builder'])->name('templates.builder');
    Route::post('/templates/{id}/fields',        [AdminTemplateController::class, 'saveFields'])->name('templates.fields.save');
    Route::post('/templates/{id}/save-layout',   [AdminTemplateController::class, 'saveLayout'])->name('templates.layout.save');
    Route::post('/templates/{id}/positions',     [AdminTemplateController::class, 'assignPositions'])->name('templates.positions.save');
    Route::post('/templates/{id}/cell-text',     [AdminTemplateController::class, 'updateCellText'])->name('templates.cell.text');
    Route::post('/templates/{id}/cell-align',    [AdminTemplateController::class, 'updateCellAlign'])->name('templates.cell.align');
    Route::post('/templates/{id}/upload-image',  [AdminTemplateController::class, 'uploadCellImage'])->name('templates.cell.image');
    Route::post('/templates/{id}/merge-cells',   [AdminTemplateController::class, 'saveMergedCells'])->name('templates.merge.cells');
    Route::put('/templates/{id}',                [AdminTemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{id}',             [AdminTemplateController::class, 'destroy'])->name('templates.destroy');

    // ─── Submission Management ────────────────────────────────────────────────
    Route::get('/submissions',                [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{id}',           [AdminSubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{id}/approve',  [AdminSubmissionController::class, 'approve'])->name('submissions.approve');
    Route::post('/submissions/{id}/reject',   [AdminSubmissionController::class, 'reject'])->name('submissions.reject');
    Route::get('/submissions/{id}/download',  [AdminSubmissionController::class, 'download'])->name('submissions.download');

    // ─── User Management ─────────────────────────────────────────────────────
    Route::get('/users',          [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}',     [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}',     [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}',  [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/approve', [AdminUserController::class, 'approve'])->name('users.approve');

    // ─── Position Management ──────────────────────────────────────────────────
    Route::get('/positions',          [AdminPositionController::class, 'index'])->name('positions.index');
    Route::post('/positions',         [AdminPositionController::class, 'store'])->name('positions.store');
    Route::put('/positions/{id}',     [AdminPositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{id}',  [AdminPositionController::class, 'destroy'])->name('positions.destroy');

    // ─── Dashboard API ────────────────────────────────────────────────────────
    Route::get('/api/recent-submissions', function () {
        try {
            $rows = \App\Models\IpcrfSubmission::with(['user', 'template'])
                ->latest('submitted_at')
                ->take(10)
                ->get()
                ->map(function ($s) {
                    return [
                        'id'            => $s->id,
                        'employee_name' => $s->user?->name ?? 'Unknown',
                        'employee_id'   => $s->user?->employee_id ?? '',
                        'template_name' => $s->template?->name ?? 'N/A',
                        'province_name' => $s->user?->assigned_province ?? ($s->employee?->school?->municipality?->province?->name ?? 'N/A'),
                        'uploaded_at'   => optional($s->submitted_at)->toIso8601String(),
                        'status'        => ucfirst(str_replace('_', ' ', $s->status)),
                        'status_raw'    => $s->status,
                    ];
                });

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('api.recent-submissions');
    });
});

// ─── User Form-Filling Routes ─────────────────────────────────────────────────
Route::prefix('my')->name('user.')->group(function () {
    Route::get('/dashboard-data',               [UserDashboardController::class, 'index'])->name('dashboard.data');
    Route::get('/templates/{id}/fill',          [UserDashboardController::class, 'fillForm'])->name('templates.fill');
    Route::post('/templates/{id}/draft',        [UserDashboardController::class, 'saveDraft'])->name('templates.draft');
    Route::post('/templates/{id}/submit',       [UserDashboardController::class, 'submit'])->name('templates.submit');
    Route::get('/submissions/{id}/download',    [UserDashboardController::class, 'download'])->name('submissions.download');
    Route::post('/submissions/{id}/upload-picture/{fieldId}', [UserDashboardController::class, 'uploadPicture'])->name('submissions.upload-picture');
    Route::get('/history',                      [UserDashboardController::class, 'submissionHistory'])->name('history');
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
Route::post('/profile/request-role-change', [AuthController::class, 'requestRoleChange'])->name('profile.requestRoleChange');

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
    
    // Verify Google reCAPTCHA
    $recaptchaResponse = $data['g_recaptcha_response'] ?? '';
    $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');
    if (empty($recaptchaResponse) || empty($recaptchaSecret)) {
        return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed.']);
    }
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $captchaResult = json_decode($verifyResponse);
    if (!$captchaResult->success) {
        return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed.']);
    }
    
    $user = \App\Models\User::where('email', $email)
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
        'id' => $user->id,
        'employee_id' => $user->employee_id,
        'name' => $user->name,
        'role' => $user->role,
        'email' => $user->email,
        'firstname' => $user->firstname,
        'lastname' => $user->lastname,
        'position' => $user->position
    ]);
    
    $redirectUrl = '/home';
    switch ($user->role) {
        case 'encoder':
            $redirectUrl = '/encoder';
            break;
        case 'admin':
            $redirectUrl = '/admins';
            if ($user->adminPositionType() === 'poo') {
                $redirectUrl = '/admin/poo/dashboard';
            }
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
    Route::post('/users/{id}/approve-role', [SuperadminController::class, 'approveRoleChange'])->name('users.approveRole');
    Route::delete('/users/{id}/reject-role', [SuperadminController::class, 'rejectRoleChange'])->name('users.rejectRole');
    Route::post('/admin/create', [SuperadminController::class, 'createAdmin'])->name('admin.create');
});

