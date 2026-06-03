@extends('admin.layouts.admin')

@section('title', 'POO Admin Dashboard')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
    .sidebar-gradient { background: linear-gradient(180deg, #1e3a5f 0%, #0f172a 100%); }
    .nav-item { transition: all 0.2s; border-left: 4px solid transparent; }
    .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); border-left-color: #38bdf8; }
    .glass-panel { background: rgba(255,255,255,0.98); border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 24px rgba(15,23,42,0.06); }
    .ipcrf-grid-wrap { overflow: auto; max-height: 55vh; border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #fff; }
    .ipcrf-grid-wrap table { border-collapse: collapse; font-size: 12px; }
    .ipcrf-grid-wrap td, .ipcrf-grid-wrap th { border: 1px solid #cbd5e1; padding: 4px 8px; min-width: 40px; }
    .status-submitted { background: #dbeafe; color: #1d4ed8; }
    .status-under_review { background: #ffedd5; color: #c2410c; }
    .status-approved { background: #dcfce7; color: #15803d; }
    .status-draft { background: #f1f5f9; color: #475569; }
    .status-rejected { background: #fee2e2; color: #b91c1c; }
</style>

<div class="flex h-screen overflow-hidden" x-data="pooDashboard()" x-init="init()">
    <aside class="sidebar-gradient w-64 flex-shrink-0 text-white flex flex-col shadow-xl z-20">
        <div class="p-6 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">POO Admin</h1>
                    <p class="text-xs text-sky-200/80">Provincial Quality Control</p>
                </div>
            </div>
            @if($assignedProvince)
            <p class="mt-3 text-xs bg-white/10 rounded-lg px-3 py-2 text-sky-100">
                <i class="fas fa-location-dot mr-1"></i> {{ $assignedProvince }}
            </p>
            @else
            <p class="mt-3 text-xs bg-amber-500/20 text-amber-100 rounded-lg px-3 py-2">
                No province assigned — contact superadmin.
            </p>
            @endif
        </div>

        <nav class="flex-1 py-4 space-y-0.5 overflow-y-auto">
            <button type="button" @click="setView('home')" class="nav-item w-full flex items-center gap-3 px-6 py-3 text-sm text-left" :class="view === 'home' && 'active'">
                <i class="fas fa-home w-5"></i> Dashboard
            </button>
            <button type="button" @click="setView('queue'); loadQueue()" class="nav-item w-full flex items-center gap-3 px-6 py-3 text-sm text-left" :class="view === 'queue' && 'active'">
                <i class="fas fa-list-check w-5"></i> Provincial Queue
                <span x-show="stats.pending > 0" x-text="stats.pending" class="ml-auto bg-amber-500 text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
            </button>
            <button type="button" @click="setView('review')" class="nav-item w-full flex items-center gap-3 px-6 py-3 text-sm text-left" :class="view === 'review' && 'active'">
                <i class="fas fa-table w-5"></i> Inspect & Review
            </button>
            <button type="button" @click="setView('directory'); loadStaff()" class="nav-item w-full flex items-center gap-3 px-6 py-3 text-sm text-left" :class="view === 'directory' && 'active'">
                <i class="fas fa-address-book w-5"></i> Staff Directory
            </button>
            <button type="button" @click="setView('archives'); loadArchives()" class="nav-item w-full flex items-center gap-3 px-6 py-3 text-sm text-left" :class="view === 'archives' && 'active'">
                <i class="fas fa-archive w-5"></i> Provincial Archives
            </button>
        </nav>

        <div class="p-4 border-t border-white/10">
            <p class="text-sm font-medium truncate">{{ $currentUser->name ?? 'POO Admin' }}</p>
            <p class="text-xs text-sky-200/70 truncate">{{ $currentUser->email ?? '' }}</p>
        </div>
    </aside>

    <main class="flex-1 overflow-auto">
        <header class="glass-panel sticky top-0 z-10 px-8 py-4 flex justify-between items-center border-b border-slate-200 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800" x-text="pageTitle"></h2>
                <p class="text-sm text-slate-500">Provincial Operations Office — IPCRF verification</p>
            </div>
            <button @click="showLogoutConfirm()" class="text-sm text-slate-600 hover:text-red-600 font-medium"><i class="fas fa-sign-out-alt mr-1"></i> Logout</button>
        </header>

        <div class="px-8 pb-10">
            <!-- Home -->
            <section x-show="view === 'home'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="glass-panel p-5 border-l-4 border-amber-500">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Pending Queue</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $stats['pending_queue'] }}</p>
                    </div>
                    <div class="glass-panel p-5 border-l-4 border-green-500">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Approved</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $stats['approved'] }}</p>
                    </div>
                    <div class="glass-panel p-5 border-l-4 border-blue-500">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Provincial Staff</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $stats['provincial_staff'] }}</p>
                    </div>
                    <div class="glass-panel p-5 border-l-4 border-indigo-500">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Archived Records</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $stats['archived'] }}</p>
                    </div>
                </div>
                <div class="glass-panel p-6">
                    <h3 class="font-bold text-slate-800 mb-2">POO Admin Responsibilities</h3>
                    <ul class="text-sm text-slate-600 space-y-2 list-disc pl-5">
                        <li>Monitor submitted IPCRFs from staff in your province</li>
                        <li>Inspect forms in a read-only spreadsheet view</li>
                        <li>Return forms for correction or approve sealed records</li>
                        <li>Search provincial staff and access approved archives</li>
                    </ul>
                    <button type="button" @click="setView('queue'); loadQueue()" class="mt-4 px-5 py-2.5 bg-sky-600 text-white rounded-lg text-sm font-semibold hover:bg-sky-700">Open Provincial Queue</button>
                </div>
            </section>

            <!-- Queue -->
            <section x-show="view === 'queue'" x-cloak class="space-y-4">
                <div class="glass-panel p-6">
                    <div class="flex flex-wrap gap-3 mb-4">
                        <select x-model="queueFilters.status" @change="loadQueue()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">All statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under review</option>
                            <option value="approved">Approved</option>
                            <option value="draft">Draft</option>
                        </select>
                        <input type="date" x-model="queueFilters.date" @change="loadQueue()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                        <input type="search" x-model="queueFilters.search" @input.debounce.400ms="loadQueue()" placeholder="Search staff..." class="flex-1 min-w-[200px] px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-slate-200 text-slate-600">
                                    <th class="pb-3 pr-4">Employee</th>
                                    <th class="pb-3 pr-4">Template</th>
                                    <th class="pb-3 pr-4">Submitted</th>
                                    <th class="pb-3 pr-4">Status</th>
                                    <th class="pb-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in queue" :key="row.id">
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="py-3 pr-4">
                                            <div class="font-medium text-slate-800" x-text="row.employee"></div>
                                            <div class="text-xs text-slate-500" x-text="row.position"></div>
                                        </td>
                                        <td class="py-3 pr-4" x-text="row.template"></td>
                                        <td class="py-3 pr-4 text-slate-500" x-text="row.submitted_at"></td>
                                        <td class="py-3 pr-4">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="'status-' + row.status" x-text="row.status_label"></span>
                                        </td>
                                        <td class="py-3">
                                            <button type="button" @click="openReview(row.id)" class="text-sky-600 font-medium text-xs hover:underline">Inspect & Review</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="queue.length === 0 && !loading">
                                    <td colspan="5" class="py-8 text-center text-slate-400">No submissions in queue.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Review -->
            <section x-show="view === 'review'" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 glass-panel p-4">
                        <h4 class="font-bold text-slate-800 mb-3">Pending Reviews</h4>
                        <button type="button" @click="loadQueue()" class="text-xs text-sky-600 mb-2 hover:underline">Refresh list</button>
                        <div class="space-y-2 max-h-[70vh] overflow-y-auto">
                            <template x-for="row in queue.filter(r => ['submitted','under_review'].includes(r.status))" :key="'r'+row.id">
                                <button type="button" @click="openReview(row.id)" class="w-full text-left p-3 rounded-lg border border-slate-200 hover:border-sky-400 hover:bg-sky-50/50 transition"
                                    :class="selectedId === row.id && 'border-sky-500 bg-sky-50'">
                                    <p class="font-medium text-sm text-slate-800" x-text="row.employee"></p>
                                    <p class="text-xs text-slate-500" x-text="row.template"></p>
                                </button>
                            </template>
                            <p x-show="queue.filter(r => ['submitted','under_review'].includes(r.status)).length === 0" class="text-sm text-slate-400 text-center py-4">No pending forms.</p>
                        </div>
                    </div>
                    <div class="lg:col-span-2 space-y-4">
                        <div class="glass-panel p-6" x-show="!selectedId">
                            <p class="text-slate-500 text-center py-12">Select a submission to inspect the web grid and write feedback.</p>
                        </div>
                        <template x-if="selectedId">
                            <div class="space-y-4">
                                <div class="glass-panel p-4 flex flex-wrap justify-between items-start gap-3">
                                    <div>
                                        <h4 class="font-bold text-slate-800" x-text="inspect.employee"></h4>
                                        <p class="text-sm text-slate-500"><span x-text="inspect.template"></span> · <span x-text="inspect.status_label"></span></p>
                                    </div>
                                    <a x-show="inspect.status === 'approved'" :href="inspect.download_url" class="text-sm text-indigo-600 font-semibold hover:underline"><i class="fas fa-download"></i> Download Excel</a>
                                </div>
                                <div class="glass-panel p-4">
                                    <h5 class="text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Read-only Form Grid</h5>
                                    <div class="ipcrf-grid-wrap" x-html="gridHtml"></div>
                                </div>
                                <div class="glass-panel p-6" x-show="['submitted','under_review'].includes(inspect.status)">
                                    <h5 class="font-bold text-slate-800 mb-3">Performance Feedback</h5>
                                    <textarea x-model="feedback" rows="5" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Corrections, recommendations, or notes for the staff member..."></textarea>
                                    <div class="flex flex-wrap gap-3 mt-4">
                                        <button type="button" @click="returnForCorrection()" :disabled="actionLoading" class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 disabled:opacity-50">
                                            Return for Correction
                                        </button>
                                        <button type="button" @click="approveSubmission()" :disabled="actionLoading" class="px-5 py-2.5 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 disabled:opacity-50">
                                            Approve Form
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-3">Returning unlocks the form as draft with your notes. Approving seals the record and enables official Excel download.</p>
                                </div>
                                <div class="glass-panel p-4 bg-amber-50 border-amber-200" x-show="inspect.admin_remarks && inspect.status === 'draft'">
                                    <p class="text-xs font-bold text-amber-800 uppercase mb-1">Previous feedback</p>
                                    <p class="text-sm text-amber-900" x-text="inspect.admin_remarks"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <!-- Directory -->
            <section x-show="view === 'directory'" x-cloak class="glass-panel p-6">
                <input type="search" x-model="staffSearch" @input.debounce.400ms="loadStaff()" placeholder="Search by name or employee ID..." class="w-full max-w-md px-4 py-2 border border-slate-200 rounded-lg text-sm mb-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-slate-200 text-slate-600">
                                <th class="pb-3 pr-4">Name</th>
                                <th class="pb-3 pr-4">Employee ID</th>
                                <th class="pb-3 pr-4">Position</th>
                                <th class="pb-3 pr-4">Office</th>
                                <th class="pb-3">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="s in staff" :key="s.id">
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pr-4 font-medium" x-text="s.name"></td>
                                    <td class="py-3 pr-4" x-text="s.employee_id"></td>
                                    <td class="py-3 pr-4" x-text="s.position"></td>
                                    <td class="py-3 pr-4" x-text="s.office"></td>
                                    <td class="py-3 text-slate-500" x-text="s.email"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Archives -->
            <section x-show="view === 'archives'" x-cloak class="glass-panel p-6">
                <div class="flex gap-3 mb-4">
                    <input type="search" x-model="archiveSearch" @input.debounce.400ms="loadArchives()" placeholder="Search employee..." class="px-4 py-2 border border-slate-200 rounded-lg text-sm">
                    <input type="number" x-model="archiveYear" @change="loadArchives()" placeholder="Year" class="w-28 px-3 py-2 border border-slate-200 rounded-lg text-sm">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-slate-200 text-slate-600">
                                <th class="pb-3 pr-4">Employee</th>
                                <th class="pb-3 pr-4">Template</th>
                                <th class="pb-3 pr-4">Approved</th>
                                <th class="pb-3">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="a in archives" :key="a.id">
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pr-4 font-medium" x-text="a.employee"></td>
                                    <td class="py-3 pr-4" x-text="a.template"></td>
                                    <td class="py-3 pr-4" x-text="a.approved_at"></td>
                                    <td class="py-3">
                                        <a :href="a.download_url" class="text-indigo-600 font-medium text-xs hover:underline"><i class="fas fa-file-excel"></i> Excel</a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- Alert modal -->
    <div x-show="modal.open" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="modal.open = false">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="font-bold text-lg mb-2" :class="modal.type === 'error' ? 'text-red-600' : 'text-slate-800'" x-text="modal.title"></h3>
            <p class="text-sm text-slate-600" x-text="modal.message"></p>
            <button type="button" @click="modal.open = false" class="mt-4 w-full py-2.5 bg-slate-800 text-white rounded-lg font-semibold text-sm">OK</button>
        </div>
    </div>

    <!-- Logout confirmation modal -->
    <div x-show="logoutModal.open" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="logoutModal.open = false">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="font-bold text-lg mb-2 text-slate-800">Confirm Logout</h3>
            <p class="text-sm text-slate-600 mb-6">Are you sure you want to logout? Any unsaved changes will be lost.</p>
            <div class="flex gap-3">
                <button type="button" @click="logoutModal.open = false" class="flex-1 py-2.5 bg-slate-200 text-slate-800 rounded-lg font-semibold text-sm hover:bg-slate-300">Cancel</button>
                <button type="button" @click="logout()" class="flex-1 py-2.5 bg-red-600 text-white rounded-lg font-semibold text-sm hover:bg-red-700">Logout</button>
            </div>
        </div>
    </div>
</div>

<script>
function pooDashboard() {
    return {
        view: 'home',
        pageTitle: 'POO Dashboard',
        queue: [],
        staff: [],
        archives: [],
        selectedId: null,
        inspect: {},
        gridHtml: '',
        feedback: '',
        loading: false,
        actionLoading: false,
        stats: { pending: {{ $stats['pending_queue'] }} },
        queueFilters: { status: '', date: '', search: '' },
        staffSearch: '',
        archiveSearch: '',
        archiveYear: '',
        modal: { open: false, title: '', message: '', type: 'info' },
        logoutModal: { open: false },

        init() {
            this.loadQueue();
        },

        setView(name) {
            this.view = name;
            const titles = {
                home: 'POO Dashboard',
                queue: 'Provincial Queue',
                review: 'Inspect & Review Forms',
                directory: 'Provincial Staff Directory',
                archives: 'Provincial Archives',
            };
            this.pageTitle = titles[name] || 'POO Admin';
            if (name === 'review' && this.queue.length === 0) this.loadQueue();
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        showModal(title, message, type = 'info') {
            this.modal = { open: true, title, message, type };
        },

        showLogoutConfirm() {
            this.logoutModal.open = true;
        },

        logout() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('logout') }}';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = this.csrf();
            
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        },

        async loadQueue() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.queueFilters);
                const res = await fetch(`{{ route('admin.poo.queue') }}?${params}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.queue = data.submissions || [];
                this.stats.pending = data.pending_count ?? 0;
            } catch (e) {
                this.showModal('Error', 'Could not load provincial queue.', 'error');
            }
            this.loading = false;
        },

        async loadStaff() {
            try {
                const params = new URLSearchParams({ search: this.staffSearch });
                const res = await fetch(`{{ route('admin.poo.staff') }}?${params}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.staff = data.staff || [];
            } catch (e) {
                this.showModal('Error', 'Could not load staff directory.', 'error');
            }
        },

        async loadArchives() {
            try {
                const params = new URLSearchParams({ search: this.archiveSearch, year: this.archiveYear });
                const res = await fetch(`{{ route('admin.poo.archives') }}?${params}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.archives = data.archives || [];
            } catch (e) {
                this.showModal('Error', 'Could not load archives.', 'error');
            }
        },

        async openReview(id) {
            this.selectedId = id;
            this.view = 'review';
            this.pageTitle = 'Inspect & Review Forms';
            this.gridHtml = '<p class="p-4 text-slate-400">Loading grid...</p>';
            try {
                const res = await fetch(`{{ url('admin/poo/submissions') }}/${id}/inspect`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Failed to load');
                this.inspect = data.submission || {};
                this.inspect.download_url = data.submission?.status === 'approved'
                    ? `{{ url('admin/poo/submissions') }}/${id}/download` : null;
                this.gridHtml = data.html_table || '<p class="p-4 text-slate-400">No grid data.</p>';
                this.feedback = data.submission?.admin_remarks || '';
            } catch (e) {
                this.showModal('Error', e.message || 'Could not load form.', 'error');
            }
        },

        async returnForCorrection() {
            if (!this.feedback.trim()) {
                this.showModal('Feedback required', 'Please enter corrections or notes before returning the form.', 'error');
                return;
            }
            this.actionLoading = true;
            try {
                const res = await fetch(`{{ url('admin/poo/submissions') }}/${this.selectedId}/return`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ remarks: this.feedback }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Request failed');
                this.showModal('Returned', data.message || 'Form returned for correction.');
                this.selectedId = null;
                await this.loadQueue();
            } catch (e) {
                this.showModal('Error', e.message || 'Could not return form.', 'error');
            }
            this.actionLoading = false;
        },

        async approveSubmission() {
            this.actionLoading = true;
            try {
                const res = await fetch(`{{ url('admin/poo/submissions') }}/${this.selectedId}/approve`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ remarks: this.feedback }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Request failed');
                this.showModal('Approved', data.message || 'Form approved.');
                if (data.download_url) this.inspect.download_url = data.download_url;
                await this.loadQueue();
            } catch (e) {
                this.showModal('Error', e.message || 'Could not approve form.', 'error');
            }
            this.actionLoading = false;
        },
    };
}
</script>
@endsection
