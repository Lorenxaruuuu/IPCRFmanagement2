<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Superadmin Dashboard - DSWD IPCR Management</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">

<div class="min-h-screen flex" x-data="{ showLogoutModal: false, showCreateAdminModal: false, activeSection: 'approvals', roleFilter: 'all' }">

    <!-- SIDEBAR -->
    <aside class="fixed left-0 top-0 h-full w-64 bg-slate-900 text-white flex flex-col shadow-xl z-20">
        <!-- Logo Header -->
        <div class="p-6 flex items-center gap-3 border-b border-slate-800">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center overflow-hidden shadow-md">
                <img src="{{ asset('images/logo.png') }}" class="w-8 h-8 object-contain" alt="DSWD Logo">
            </div>
            <div>
                <h1 class="font-extrabold text-sm tracking-wider text-slate-100 uppercase">DSWD Portal</h1>
                <p class="text-xs text-blue-400 font-semibold">Superadmin View</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 py-6 px-4 space-y-2">
            <button @click="activeSection = 'approvals'" 
                    :class="activeSection === 'approvals' ? 'bg-blue-600/10 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all text-left">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                Account Approvals
            </button>
            
            <button @click="activeSection = 'users'" 
                    :class="activeSection === 'users' ? 'bg-blue-600/10 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all text-left">
                <i data-lucide="users" class="w-5 h-5"></i>
                All System Users
            </button>

            <button @click="activeSection = 'admins'" 
                    :class="activeSection === 'admins' ? 'bg-blue-600/10 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all text-left">
                <i data-lucide="shield" class="w-5 h-5"></i>
                System Administrators
            </button>
            
            <button @click="showCreateAdminModal = true" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition-all text-left">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Create Admin
            </button>
        </nav>

        <!-- User Information & Logout -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 px-4 py-3 bg-slate-800/40 rounded-2xl mb-2">
                <div class="w-9 h-9 bg-blue-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/20">
                    SA
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate text-slate-100">Super Admin</p>
                    <p class="text-xs text-slate-400 truncate">superadmin@dswd.gov.ph</p>
                </div>
            </div>
                    <span x-show="activeSection === 'approvals'">Superadmin Control Center</span>
                    <span x-show="activeSection === 'users'">All System Users</span>
                    <span x-show="activeSection === 'admins'">System Administrators</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    <span x-show="activeSection === 'approvals'">Manage system access, approve credentials, and register administrators</span>
                    <span x-show="activeSection === 'users'">Complete user registry including pending and approved accounts</span>
                    <span x-show="activeSection === 'admins'">All administrators and superadmins in the system</span>
                
            <!-- Logout Button -->
            <button @click="showLogoutModal = true" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-all font-medium">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                Sign Out
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="ml-64 flex-1 flex flex-col min-w-0">
        
        <!-- HEADER -->
        <header class="bg-white border-b h-20 flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    <span x-show="activeSection === 'approvals'">Superadmin Control Center</span>
                    <span x-show="activeSection === 'users'">All System Users</span>
                    <span x-show="activeSection === 'admins'">System Administrators</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    <span x-show="activeSection === 'approvals'">Manage system access, approve credentials, and register administrators</span>
                    <span x-show="activeSection === 'users'">Complete user registry including pending and approved accounts</span>
                    <span x-show="activeSection === 'admins'">All administrators and superadmins in the system</span>
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    System Online
                </span>
            </div>
        </header>

        <!-- DASHBOARD BODY -->
        <main class="p-8 space-y-8 max-w-[1600px] mx-auto w-full">

            <!-- ALERTS / NOTIFICATIONS -->
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm animate-fade-in">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm space-y-1">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                        <p class="text-sm font-bold">Please correct the following errors:</p>
                    </div>
                    <ul class="list-disc pl-8 text-xs font-medium space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- ACCOUNT APPROVALS SECTION -->
            <div x-show="activeSection === 'approvals'" class="space-y-8">
                <!-- STATS CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Stat: Pending Approvals -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pending Approvals</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 shadow-inner">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <!-- Stat: Active Users -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Approved Users</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $stats['active'] }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 shadow-inner">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <!-- Stat: Administrators -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Administrators</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $stats['admins'] }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 shadow-inner">
                            <i data-lucide="shield" class="w-6 h-6"></i>
                        </div>
                    </div>

                    <!-- Stat: Encoders -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Encoders</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $stats['encoders'] }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 shadow-inner">
                            <i data-lucide="edit" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <!-- MAIN GRID CONTENT -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- PENDING APPROVALS LIST -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800">Pending Registrations</h3>
                                    <p class="text-xs text-slate-500">Accounts awaiting verification to gain system access</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    {{ $pendingUsers->count() }} Pending
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="text-xs font-bold text-slate-400 border-b border-slate-100 bg-slate-50/20 uppercase tracking-wider">
                                            <th class="py-4 px-6 font-semibold">User Details</th>
                                            <th class="py-4 px-6 font-semibold">Employee ID</th>
                                            <th class="py-4 px-6 font-semibold text-center">Requested Role</th>
                                            <th class="py-4 px-6 font-semibold text-center">Registered</th>
                                            <th class="py-4 px-6 font-semibold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @forelse($pendingUsers as $user)
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="py-4 px-6">
                                                    <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                                </td>
                                                <td class="py-4 px-6 font-mono text-slate-600 font-semibold">
                                                    {{ $user->employee_id }}
                                                </td>
                                                <td class="py-4 px-6 text-center">
                                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold capitalize 
                                                        @if($user->role === 'admin') bg-red-100 text-red-800 border border-red-200
                                                        @elseif($user->role === 'encoder') bg-indigo-100 text-indigo-800 border border-indigo-200
                                                        @elseif($user->role === 'staff' &&
$user->role !== 'admin' &&
$user->role !== 'encoder') bg-blue-100 text-blue-800 border border-blue-200
                                                        @else bg-slate-100 text-slate-800 border border-slate-200 @endif">
                                                        {{ $user->role }}
                                                    </span>
                                                </td>
                                                <td class="py-4 px-6 text-center text-xs text-slate-500 font-medium">
                                                    {{ $user->created_at ? $user->created_at->diffForHumans() : 'N/A' }}
                                                </td>
                                                <td class="py-4 px-6 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <!-- Approve -->
                                                        <form action="{{ route('superadmin.users.approve', $user->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl text-emerald-600 hover:bg-emerald-50 border border-transparent hover:border-emerald-200 transition-all" title="Approve Account">
                                                                <i data-lucide="check" class="w-5 h-5"></i>
                                                            </button>
                                                        </form>
                                                        <!-- Reject -->
                                                        <form action="{{ route('superadmin.users.reject', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this request? It will be deleted permanently.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-all" title="Reject Account">
                                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-12 text-center text-slate-400">
                                                    <div class="max-w-xs mx-auto space-y-3">
                                                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                                                            <i data-lucide="user-check" class="w-6 h-6"></i>
                                                        </div>
                                                        <p class="text-sm font-semibold">All registrations processed!</p>
                                                        <p class="text-xs text-slate-400">New registration requests will appear here for superadmin approval.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIVE / APPROVED USERS & STATS -->
                    <div class="space-y-6">
                        <!-- Create Administrator Quick Form -->
                        <div class="bg-slate-900 text-white rounded-3xl shadow-lg border border-slate-800 p-6 flex flex-col">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-blue-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-100">Administrator Registry</h4>
                                    <p class="text-xs text-slate-400">Instantly provision verified admin accounts</p>
                                </div>
                            </div>

                            <form action="{{ route('superadmin.admin.create') }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">First Name</label>
                                        <input type="text" name="firstname" required class="w-full bg-slate-800 border border-slate-700/80 rounded-xl px-3 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. Marie">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Last Name</label>
                                        <input type="text" name="lastname" required class="w-full bg-slate-800 border border-slate-700/80 rounded-xl px-3 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. Santos">
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Employee ID</label>
                                    <input type="text" name="employee_id" required class="w-full bg-slate-800 border border-slate-700/80 rounded-xl px-3 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. ADMIN-02">
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Position</label>
                                    <select name="position" required class="w-full bg-slate-800 border border-slate-700/80 rounded-xl px-3 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-blue-500 transition-colors">
                                        <option value="" class="bg-slate-800 text-slate-100">Select Position</option>
                                        <option value="rpmo" class="bg-slate-800 text-slate-100">RPMO (Regional Program Management Officer)</option>
                                        <option value="poo" class="bg-slate-800 text-slate-100">POO (Provincial Operations Officer)</option>
                                        <option value="rpmo_poo" class="bg-slate-800 text-slate-100">RPMO & POO</option>
                                        <option value="none" class="bg-slate-800 text-slate-100">None</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Password</label>
                                    <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-700/80 rounded-xl px-3 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors" placeholder="Minimum 8 characters">
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Confirm Password</label>
                                    <input type="password" name="password_confirmation" required class="w-full bg-slate-800 border border-slate-700/80 rounded-xl px-3 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors" placeholder="Re-enter password">
                                </div>

                                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2 mt-2">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    Provision Admin Account
                                </button>
                            </form>
                        </div>

                        <!-- ACTIVE SYSTEM USERS -->
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h4 class="font-bold text-slate-800">Approved System Users</h4>
                                <span class="text-xs bg-slate-100 px-2.5 py-1 rounded-full text-slate-600 font-semibold">{{ $activeUsers->count() }} Total</span>
                            </div>

                            <div class="space-y-3 max-h-[350px] overflow-y-auto pr-1">
                                @forelse($activeUsers as $user)
                                    <div class="flex items-center justify-between p-3 bg-slate-50/50 hover:bg-slate-50 border border-slate-100 rounded-2xl transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-slate-800 text-sm truncate">{{ $user->name }}</div>
                                            <div class="text-[11px] text-slate-400 font-medium font-mono truncate">{{ $user->employee_id }}</div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize border 
                                            @if($user->role === 'admin') bg-red-50 text-red-700 border-red-100
                                            @elseif($user->role === 'encoder') bg-indigo-50 text-indigo-700 border-indigo-100
                                            @elseif($user->role === 'staff') bg-blue-50 text-blue-700 border-blue-100
                                            @else bg-slate-50 text-slate-700 border-slate-100 @endif">
                                            {{ $user->role }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-slate-400 text-xs">No active users yet in the system.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ALL USERS SECTION -->
            <div x-show="activeSection === 'users'" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs text-slate-500 mt-1">Complete user registry including pending and approved accounts</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            {{ $pendingUsers->count() + $activeUsers->count() }} Users
                        </span>
                    </div>
                    
                    <!-- ROLE FILTER BUTTONS -->
                    <div class="flex flex-wrap gap-2">
                        <button @click="roleFilter = 'all'" 
                                :class="roleFilter === 'all' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all">
                            All Roles
                        </button>
                        <button @click="roleFilter = 'admin'" 
                                :class="roleFilter === 'admin' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all">
                            Administrator
                        </button>
                        <button @click="roleFilter = 'encoder'" 
                                :class="roleFilter === 'encoder' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all">
                            Encoder
                        </button>
                        <button @click="roleFilter = 'staff'" 
                                :class="roleFilter === 'staff' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all">
                            Staff
                        </button>
                        <button @click="roleFilter = 'superadmin'" 
                                :class="roleFilter === 'superadmin' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all">
                            Superadmin
                        </button>
                    </div>

                    <!-- PENDING ROLE CHANGES LIST -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col mt-6">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Pending Role Changes</h3>
                                <p class="text-xs text-slate-500">Users requesting to change their current role</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                {{ $pendingRoleChanges->count() }} Pending
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-xs font-bold text-slate-400 border-b border-slate-100 bg-slate-50/20 uppercase tracking-wider">
                                        <th class="py-4 px-6 font-semibold">User Details</th>
                                        <th class="py-4 px-6 font-semibold text-center">Current Role</th>
                                        <th class="py-4 px-6 font-semibold text-center">Requested Role</th>
                                        <th class="py-4 px-6 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @forelse($pendingRoleChanges as $user)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-4 px-6">
                                                <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                                <div class="text-xs text-slate-400 font-mono">{{ $user->employee_id }}</div>
                                            </td>
                                            <td class="py-4 px-6 text-center">
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold capitalize bg-slate-100 text-slate-800 border border-slate-200">
                                                    {{ $user->role }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 text-center">
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold capitalize bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                    {{ $user->requested_role }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <!-- Approve -->
                                                    <form action="{{ route('superadmin.users.approveRole', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to approve this role change?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl text-emerald-600 hover:bg-emerald-50 border border-transparent hover:border-emerald-200 transition-all" title="Approve Role Change">
                                                            <i data-lucide="check" class="w-5 h-5"></i>
                                                        </button>
                                                    </form>
                                                    <!-- Reject -->
                                                    <form action="{{ route('superadmin.users.rejectRole', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this role change request?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-all" title="Reject Role Change">
                                                            <i data-lucide="x" class="w-5 h-5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-12 text-center text-slate-400">
                                                <div class="max-w-xs mx-auto space-y-3">
                                                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                                                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                                                    </div>
                                                    <p class="text-sm font-semibold">No pending role changes</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-slate-400 border-b border-slate-100 bg-slate-50/20 uppercase tracking-wider">
                                <th class="py-4 px-6 font-semibold">Name</th>
                                <th class="py-4 px-6 font-semibold">Email</th>
                                <th class="py-4 px-6 font-semibold">Employee ID</th>
                                <th class="py-4 px-6 font-semibold">Role</th>
                                <th class="py-4 px-6 font-semibold text-center">Status</th>
                                <th class="py-4 px-6 font-semibold">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @php
                                $allUsers = array_merge($pendingUsers->all(), $activeUsers->all());
                                // Sort by role
                                usort($allUsers, function($a, $b) {
                                    return strcasecmp($a->role, $b->role);
                                });
                            @endphp
                            @forelse($allUsers as $user)
                                <tr x-show="roleFilter === 'all' || roleFilter === '{{ $user->role }}' || (roleFilter === 'staff' && !['encoder', 'admin', 'superadmin'].includes('{{ $user->role }}'))" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-800">{{ $user->name }}</td>
                                    <td class="py-4 px-6 text-slate-600">{{ $user->email }}</td>
                                    <td class="py-4 px-6 font-mono text-slate-600 font-semibold">{{ $user->employee_id }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold capitalize 
                                            @if($user->role === 'admin') bg-red-100 text-red-800 border border-red-200
                                            @elseif($user->role === 'encoder') bg-indigo-100 text-indigo-800 border border-indigo-200
                                            @elseif($user->role === 'staff') bg-blue-100 text-blue-800 border border-blue-200
                                            @elseif($user->role === 'superadmin') bg-purple-100 text-purple-800 border border-purple-200
                                            @else bg-slate-100 text-slate-800 border border-slate-200 @endif">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($user->approved)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-500">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                                                <i data-lucide="users" class="w-6 h-6"></i>
                                            </div>
                                            <p class="text-sm font-semibold">No users registered</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ALL ADMINISTRATORS SECTION -->
            <div x-show="activeSection === 'admins'" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div
            </div>

            <!-- ALL ADMINISTRATORS SECTION -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">System Administrators</h3>
                            <p class="text-xs text-slate-500 mt-1">All administrators and superadmins in the system</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                            {{ $activeUsers->where('role', 'admin')->count() + 1 }} Admins
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-slate-400 border-b border-slate-100 bg-slate-50/20 uppercase tracking-wider">
                                <th class="py-4 px-6 font-semibold">Name</th>
                                <th class="py-4 px-6 font-semibold">Email</th>
                                <th class="py-4 px-6 font-semibold">Employee ID</th>
                                <th class="py-4 px-6 font-semibold">Level</th>
                                <th class="py-4 px-6 font-semibold">Status</th>
                                <th class="py-4 px-6 font-semibold">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <!-- Superadmin (from session or hardcoded for display) -->
                            @php
                                $allAdmins = $activeUsers->filter(fn($u) => $u->role === 'admin' || $u->role === 'superadmin');
                            @endphp
                            @forelse($allAdmins as $admin)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-800">{{ $admin->name }}</td>
                                    <td class="py-4 px-6 text-slate-600">{{ $admin->email }}</td>
                                    <td class="py-4 px-6 font-mono text-slate-600 font-semibold">{{ $admin->employee_id }}</td>
                                    <td class="py-4 px-6">
                                        @if($admin->role === 'superadmin')
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">Super Admin</span>
                                        @else
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">Administrator</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-500">{{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                                                <i data-lucide="shield" class="w-6 h-6"></i>
                                            </div>
                                            <p class="text-sm font-semibold">No administrators created yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div x-show="showLogoutModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl border border-slate-100 space-y-6">
            <div class="text-center space-y-3">
                <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="log-out" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Signing Out?</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Are you sure you want to end your superadmin session and return to login?</p>
            </div>
            
            <div class="flex gap-3">
                <button @click="showLogoutModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 rounded-xl text-sm font-semibold transition-all">Cancel</button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-rose-600/20">Sign Out</button>
                </form>
            </div>
        </div>
    </div>

    <!-- CREATE ADMIN MODAL -->
    <div x-show="showCreateAdminModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl border border-slate-100 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-blue-600"></i>
                    <h3 class="text-lg font-bold text-slate-800">Add New Administrator</h3>
                </div>
                <button @click="showCreateAdminModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('superadmin.admin.create') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">First Name</label>
                        <input type="text" name="firstname" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. John">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Last Name</label>
                        <input type="text" name="lastname" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. Doe">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Employee ID</label>
                    <input type="text" name="employee_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. ADMIN-99">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Position</label>
                    <select name="position" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Select Position</option>
                        <option value="rpmo">RPMO (Regional Program Management Officer)</option>
                        <option value="poo">POO (Provincial Operations Officer)</option>
                        <option value="rpmo_poo">RPMO & POO</option>
                        <option value="none">None</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Password</label>
                    <input type="password" name="password" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors" placeholder="Minimum 8 characters">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors" placeholder="Re-enter password">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showCreateAdminModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-all">Create Account</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    // Initialize Lucide icons
    document.addEventListener("DOMContentLoaded", () => {
        lucide.createIcons();
    });
</script>
</body>
</html>
