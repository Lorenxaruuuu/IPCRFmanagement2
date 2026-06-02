@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
@yield('content')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPCRF Admin Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .sidebar-gradient {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }
        
        .nav-item {
            transition: all 0.3s ease;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .priority-high { background: #fee2e2; color: #dc2626; }
        .priority-medium { background: #fef3c7; color: #d97706; }
        .priority-low { background: #dbeafe; color: #2563eb; }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .step-indicator {
            position: relative;
        }
        
        .step-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        
        .step-indicator:last-child::after {
            display: none;
        }
        
        .step-active {
            background: #3b82f6;
            color: white;
        }
        
        .step-completed {
            background: #10b981;
            color: white;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .status-submitted { background: #dcfce7; color: #166534; }
        .status-draft { background: #fef3c7; color: #854d0e; }
        .status-approved { background: #dbeafe; color: #0c4a6e; }
        .status-returned { background: #fecaca; color: #7f1d1d; }

        .feedback-box {
            background: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .grid-table th {
            background: #f3f4f6;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }

        .grid-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .grid-table tbody tr:hover {
            background: #f9fafb;
        }
    </style>

<div class="bg-gray-50" x-data="{ showLogoutModal: false, loggingOut: false, selectedForm: null, showFeedbackModal: false, showApprovalModal: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="sidebar-gradient w-64 flex-shrink-0 text-white flex flex-col">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg">IPCRF Admin</h1>
                        <p class="text-xs text-gray-400">Management System</p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 py-6">
                @if($userPosition !== 'poo')
                    <a href="#" onclick="showView('dashboard')" class="nav-item active flex items-center gap-3 px-6 py-3 text-sm" id="nav-dashboard">
                        <i class="fas fa-home w-5"></i>
                        Dashboard Home
                    </a>
                    <a href="#" onclick="showView('upload')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-upload">
                        <i class="fas fa-upload w-5"></i>
                        Update/Upload IPCRF
                    </a>
                    <a href="#" onclick="showView('records')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-records">
                        <i class="fas fa-list w-5"></i>
                        List of Uploaded
                    </a>
                    <a href="#" onclick="showView('notices')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-notices">
                        <i class="fas fa-bell w-5"></i>
                        Manage Notices
                    </a>
                    <a href="#" onclick="showView('forms')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-forms">
                        <i class="fas fa-file-alt w-5"></i>
                        Manage Forms
                    </a>
                @endif
                
                @if($userPosition === 'poo' || $userPosition === 'rpmo_poo')
                    @if($userPosition === 'rpmo_poo')
                        <hr class="my-3 border-gray-600">
                    @endif
                    <div class="px-6 py-2 text-xs font-bold uppercase text-gray-400">POO Actions</div>
                    <a href="#" onclick="showView('provincial-queue')" class="nav-item{{ $userPosition === 'poo' ? ' active' : '' }} flex items-center gap-3 px-6 py-3 text-sm" id="nav-provincial-queue">
                        <i class="fas fa-tasks w-5"></i>
                        Monitor Queue
                    </a>
                    <a href="#" onclick="showView('review-forms')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-review-forms">
                        <i class="fas fa-table w-5"></i>
                        Inspect Web Grids
                    </a>
                    <a href="#" onclick="showView('provincial-directory')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-provincial-directory">
                        <i class="fas fa-address-book w-5"></i>
                        Staff Directory
                    </a>
                    <a href="#" onclick="showView('provincial-archives')" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-provincial-archives">
                        <i class="fas fa-archive w-5"></i>
                        Archives
                    </a>
                @endif
            </nav>
            
            <div class="p-4 border-t border-gray-700"> 
                <div class="flex items-start gap-3">
                    @php
                        // Bridge native PHP session and Laravel session if needed
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $sessionUser = session('user');
                        if (!$sessionUser && isset($_SESSION['employee_id'])) {
                            $sessionUser = [
                                'employee_id' => $_SESSION['employee_id'],
                                'name' => ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''),
                                'role' => $_SESSION['role'] ?? 'admin',
                                'email' => $_SESSION['email'] ?? '',
                                'firstname' => $_SESSION['firstname'] ?? '',
                                'lastname' => $_SESSION['lastname'] ?? '',
                                'position' => $_SESSION['position'] ?? 'none'
                            ];
                            session(['user' => $sessionUser]);
                        }

                        // Fetch session-based user info
                        $employeeId = $sessionUser['employee_id'] ?? '';
                        $email = $sessionUser['email'] ?? 'admin@deped.gov.ph';
                        $name = $sessionUser['name'] ?? ($sessionUser['firstname'] ?? 'Administrator') . ' ' . ($sessionUser['lastname'] ?? '');
                        $firstname = $sessionUser['firstname'] ?? 'Administrator';
                        $lastname = $sessionUser['lastname'] ?? '';
                        $position = $sessionUser['position'] ?? 'none';
                        $role = $sessionUser['role'] ?? 'admin';

                        // Fetch DB record for the user to get permanent profile information
                        $dbUser = null;
                        if ($employeeId) {
                            try {
                                $dbUser = \App\Models\User::where('employee_id', $employeeId)->first();
                                if ($dbUser) {
                                    $employeeId = $dbUser->employee_id;
                                    $email = $dbUser->email ?? $email;
                                    $name = $dbUser->name ?? $name;
                                    $firstname = $dbUser->firstname ?? $firstname;
                                    $lastname = $dbUser->lastname ?? $lastname;
                                    $position = $dbUser->position ?? $position;
                                    $role = $dbUser->role ?? $role;
                                }
                            } catch (\Exception $e) {}
                        }

                        // Final fallback values
                        $displayName = trim($name) ?: 'Administrator';
                        $displayEmail = $email ?: 'admin@deped.gov.ph';
                        $displayFirstname = trim($firstname) ?: 'Admin';
                        $displayRole = ucfirst($role);
                        $displayPosition = $position !== 'none' ? ucfirst($position) : '';
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($displayFirstname) }}&background=3b82f6&color=fff" class="w-12 h-12 rounded-lg flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $displayName }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $displayEmail }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-block px-2 py-1 bg-blue-600 text-white text-xs rounded font-medium">{{ $displayRole }}</span>
                            @if($displayPosition)
                                <span class="inline-block px-2 py-1 bg-purple-600 text-white text-xs rounded font-medium">{{ $displayPosition }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Top Header -->
            <header class="glass-panel sticky top-0 z-40 px-8 py-4 flex justify-between items-center border-b">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800" id="page-title">Admin Dashboard Overview</h2>
                    <p class="text-sm text-gray-500" id="page-subtitle">Manage IPCRF records and system announcements</p>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="showLogoutModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </button>
                </div>
            </header>

            <div class="p-8">
                <!-- DASHBOARD VIEW -->
                <div id="view-dashboard" class="view-section fade-in{{ $userPosition === 'poo' ? ' hidden' : '' }}">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="glass-panel rounded-2xl p-6 card-hover border-l-4 border-blue-500 cursor-pointer" onclick="showView('records')">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm mb-1">IPCRF Uploaded</p>
                                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['uploaded_employees'] }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-panel rounded-2xl p-6 card-hover border-l-4 border-green-500 cursor-pointer" onclick="showView('forms')">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm mb-1">Active Forms</p>
                                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['active_forms'] }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-panel rounded-2xl p-6 card-hover border-l-4 border-orange-500 cursor-pointer" onclick="showView('notices')">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm mb-1">Notices</p>
                                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['notices'] }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-bell text-orange-600 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <div class="glass-panel rounded-2xl p-6 card-hover border-l-4 border-teal-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm mb-1">Total Employees</p>
                                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_employees'] }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-users text-teal-600 text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Submissions -->
                    <div class="grid grid-cols-1 gap-8">
                        <div class="glass-panel rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Submissions</h3>
                            <div class="overflow-x-auto">
                                <table class="grid-table">
                                    <thead>
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>School</th>
                                            <th>Status</th>
                                            <th>Uploaded</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentSubmissions as $item)
                                        <tr>
                                            <td class="font-medium">{{ $item->employee->first_name ?? $item->name }}</td>
                                            <td>{{ $item->employee->school->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="status-badge status-{{ strtolower($item->status) }}">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td>{{ $item->uploaded_at->format('M d, Y') ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('admin.records.download', $item->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">Download</a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-gray-500 py-4">No submissions yet</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POO: MONITOR PROVINCIAL QUEUE VIEW -->
                <div id="view-provincial-queue" class="view-section hidden fade-in">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Monitor Provincial Queue</h3>
                        <p class="text-gray-600">Live tracker of all Submitted IPCRFs from staff within your designated province</p>
                    </div>

                    <div class="glass-panel rounded-2xl p-6 mb-6">
                        <div class="flex gap-4 mb-6">
                            <input type="text" id="queueSearch" placeholder="Search by staff name or school..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select id="queueStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="submitted">Submitted</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="approved">Approved</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="grid-table">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Staff Name</th>
                                        <th>School</th>
                                        <th>Status</th>
                                        <th>Submitted Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="queueTable">
                                    @forelse($recentSubmissions as $item)
                                    <tr>
                                        <td>
                                            <span class="status-badge priority-{{ rand(1,3) == 1 ? 'high' : (rand(1,2) == 1 ? 'medium' : 'low') }}">
                                                {{ ['High', 'Medium', 'Low'][rand(0,2)] }}
                                            </span>
                                        </td>
                                        <td class="font-medium">{{ $item->employee->first_name ?? 'N/A' }}</td>
                                        <td>{{ $item->employee->school->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="status-badge status-submitted">{{ $item->status }}</span>
                                        </td>
                                        <td>{{ $item->uploaded_at->format('M d, Y H:i') ?? 'N/A' }}</td>
                                        <td>
                                            <button onclick="inspectForm({{ $item->id }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Inspect</button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-500 py-4">No submissions in queue</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- POO: INSPECT WEB GRIDS (REVIEW FORMS) VIEW -->
                <div id="view-review-forms" class="view-section hidden fade-in">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Inspect Web Grids</h3>
                        <p class="text-gray-600">Read-only, spreadsheet-faithful view of staff member's submitted form</p>
                    </div>

                    <div class="glass-panel rounded-2xl p-6">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Form to Review</label>
                            <select id="formSelector" onchange="loadFormData(this.value)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose a form...</option>
                                @foreach($recentSubmissions as $item)
                                <option value="{{ $item->id }}">{{ $item->employee->first_name ?? 'N/A' }} - {{ $item->employee->school->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Form Display Area -->
                        <div id="formDisplayArea" class="hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <h4 class="font-bold text-gray-800 mb-2">Form Details</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">Staff Name:</span>
                                        <span id="formStaffName" class="text-gray-600"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">School:</span>
                                        <span id="formSchool" class="text-gray-600"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Status:</span>
                                        <span id="formStatus" class="status-badge status-draft"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Submitted:</span>
                                        <span id="formDate" class="text-gray-600"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Feedback Section -->
                            <div class="mb-6">
                                <h4 class="font-bold text-gray-800 mb-3">Performance Feedback</h4>
                                <div class="feedback-box mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Write Feedback (corrections, recommendations, notes)</label>
                                    <textarea id="feedbackText" placeholder="Enter your feedback here..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"></textarea>
                                </div>

                                <div class="flex gap-3">
                                    <button onclick="submitFeedback()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                                        <i class="fas fa-check"></i> Approve Form
                                    </button>
                                    <button onclick="returnForCorrection()" class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition flex items-center gap-2">
                                        <i class="fas fa-undo"></i> Return for Correction
                                    </button>
                                </div>
                            </div>

                            <!-- Form Grid Display (Spreadsheet View) -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3">Form Data Grid</h4>
                                <div class="overflow-x-auto">
                                    <table class="grid-table">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody id="formGridData">
                                            <tr>
                                                <td colspan="2" class="text-center text-gray-500 py-4">Select a form to view details</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POO: PROVINCIAL DIRECTORY VIEW -->
                <div id="view-provincial-directory" class="view-section hidden fade-in">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Search Provincial Directory</h3>
                        <p class="text-gray-600">Filter and view profiles of staff registered within your designated province</p>
                    </div>

                    <div class="glass-panel rounded-2xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <input type="text" id="staffSearch" placeholder="Search by name..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select id="staffPosition" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Positions</option>
                                <option value="teacher">Teacher</option>
                                <option value="principal">Principal</option>
                                <option value="coordinator">Coordinator</option>
                            </select>
                            <select id="staffStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="grid-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>School</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="directoryTable">
                                    @forelse($recentSubmissions as $item)
                                    <tr>
                                        <td class="font-medium">{{ $item->employee->first_name ?? 'N/A' }} {{ $item->employee->last_name ?? '' }}</td>
                                        <td>{{ $item->employee->role ?? 'N/A' }}</td>
                                        <td>{{ $item->employee->school->name ?? 'N/A' }}</td>
                                        <td>{{ $item->employee->email ?? 'N/A' }}</td>
                                        <td>
                                            <span class="status-badge bg-green-100 text-green-700">Active</span>
                                        </td>
                                        <td>
                                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Profile</button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-500 py-4">No staff records found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- POO: PROVINCIAL ARCHIVES VIEW -->
                <div id="view-provincial-archives" class="view-section hidden fade-in">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Access Provincial Archives</h3>
                        <p class="text-gray-600">Review or download past approved IPCRF records for historical audits</p>
                    </div>

                    <div class="glass-panel rounded-2xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <select id="archiveYear" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Year</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                            </select>
                            <select id="archiveSemester" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Semester</option>
                                <option value="1">1st Semester</option>
                                <option value="2">2nd Semester</option>
                            </select>
                            <input type="text" id="archiveSearch" placeholder="Search by staff name..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="overflow-x-auto">
                            <table class="grid-table">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th>School</th>
                                        <th>Year</th>
                                        <th>Semester</th>
                                        <th>Approved Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="archivesTable">
                                    @forelse($recentSubmissions->where('status', 'Approved') as $item)
                                    <tr>
                                        <td class="font-medium">{{ $item->employee->first_name ?? 'N/A' }}</td>
                                        <td>{{ $item->employee->school->name ?? 'N/A' }}</td>
                                        <td>{{ $item->school_year ?? date('Y') }}</td>
                                        <td>{{ $item->semester ?? '1' }}</td>
                                        <td>{{ $item->uploaded_at->format('M d, Y') ?? 'N/A' }}</td>
                                        <td>
                                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-2">View</button>
                                            <a href="{{ route('admin.records.download', $item->id) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">Download</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-500 py-4">No approved records in archives</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Logout Modal -->
        <div x-show="showLogoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
            <div class="glass-panel rounded-2xl p-8 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Confirm Logout</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to logout?</p>
                <div class="flex gap-3">
                    <button @click="showLogoutModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showView(viewName) {
            // Hide all views
            document.querySelectorAll('.view-section').forEach(el => el.classList.add('hidden'));
            
            // Show selected view
            const view = document.getElementById('view-' + viewName);
            if (view) {
                view.classList.remove('hidden');
            }

            // Update nav items
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            const navItem = document.getElementById('nav-' + viewName);
            if (navItem) {
                navItem.classList.add('active');
            }

            // Update page title
            const titles = {
                'dashboard': 'Admin Dashboard Overview',
                'upload': 'Update/Upload IPCRF',
                'records': 'List of Uploaded Records',
                'notices': 'Manage Notices',
                'forms': 'Manage Forms',
                'provincial-queue': 'Monitor Provincial Queue',
                'review-forms': 'Inspect Web Grids',
                'provincial-directory': 'Provincial Staff Directory',
                'provincial-archives': 'Provincial Archives'
            };

            document.getElementById('page-title').textContent = titles[viewName] || 'Dashboard';
        }

        function inspectForm(formId) {
            document.getElementById('formSelector').value = formId;
            loadFormData(formId);
            showView('review-forms');
        }

        function loadFormData(formId) {
            if (!formId) {
                document.getElementById('formDisplayArea').classList.add('hidden');
                return;
            }

            // Display form area
            document.getElementById('formDisplayArea').classList.remove('hidden');

            // Sample data - in real app, fetch from server
            document.getElementById('formStaffName').textContent = 'John Doe';
            document.getElementById('formSchool').textContent = 'Sample School';
            document.getElementById('formStatus').textContent = 'Draft';
            document.getElementById('formDate').textContent = 'June 2, 2026';

            // Populate grid data
            const gridData = `
                <tr><td>Employee ID</td><td>EMP12345</td></tr>
                <tr><td>Name</td><td>John Doe</td></tr>
                <tr><td>Position</td><td>Teacher</td></tr>
                <tr><td>School</td><td>Sample School</td></tr>
                <tr><td>Performance Rating</td><td>Very Satisfactory</td></tr>
                <tr><td>Attendance</td><td>95%</td></tr>
                <tr><td>Core Competency</td><td>Excellent</td></tr>
            `;
            document.getElementById('formGridData').innerHTML = gridData;
        }

        function submitFeedback() {
            const feedback = document.getElementById('feedbackText').value;
            if (feedback.trim()) {
                alert('Form approved with feedback!\n\n' + feedback);
                document.getElementById('feedbackText').value = '';
            } else {
                alert('Please enter feedback before approving.');
            }
        }

        function returnForCorrection() {
            const feedback = document.getElementById('feedbackText').value;
            if (feedback.trim()) {
                alert('Form returned for correction!\n\nFeedback sent to staff:\n' + feedback);
                document.getElementById('feedbackText').value = '';
            } else {
                alert('Please enter feedback before returning.');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Auto-refresh on first load to ensure all data is properly initialized
            if (!sessionStorage.getItem('dashboardLoaded')) {
                sessionStorage.setItem('dashboardLoaded', 'true');
                // Small delay to ensure DOM is fully rendered before refresh
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }
        });
    </script>
</div>
@endsection
