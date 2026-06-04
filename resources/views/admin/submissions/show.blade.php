@extends('admin.layouts.admin')

@section('title', 'Review Submission #' . $submission->id . ' — ' . ($submission->user?->name ?? 'User'))

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        min-height: 100vh;
    }
    
    .glass-panel {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
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
    
    .ipcrf-grid-wrap {
        overflow: auto;
        max-height: 72vh;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .ipcrf-grid-wrap table {
        border-collapse: collapse;
        font-size: 11px;
        color: #1e293b;
        min-width: 100%;
    }
    
    .ipcrf-grid-wrap td, .ipcrf-grid-wrap th {
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        min-width: 42px;
    }
    
    /* Excel column/row headers */
    .ipcrf-hdr-corner, .ipcrf-hdr-col, .ipcrf-hdr-row {
        background: #f1f5f9 !important;
        color: #64748b !important;
        font-weight: 700 !important;
        font-size: 10px !important;
        text-align: center !important;
        border: 1px solid #cbd5e1 !important;
        user-select: none !important;
    }

    .field-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="flex h-screen overflow-hidden bg-slate-50">
    <!-- Sidebar (Matches Admin Dashboard Layout) -->
    <aside class="sidebar-gradient w-64 flex-shrink-0 text-white flex flex-col shadow-xl z-20">
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">IPCRF Admin</h1>
                    <p class="text-xs text-gray-400">Management System</p>
                </div>
            </div>
        </div>
        
        <nav class="flex-1 py-4 overflow-y-auto">
            <div class="px-4 mb-2"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Overview</p></div>
            <a href="{{ route('admin.dashboard') }}#dashboard" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-dashboard">
                <i class="fas fa-home w-5"></i>
                Dashboard Home
            </a>
            <div class="px-4 mt-4 mb-2"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">IPCRF Digital System</p></div>
            <a href="{{ route('admin.dashboard') }}#templates" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-templates">
                <i class="fas fa-file-excel w-5"></i>
                IPCRF Templates
                <span class="ml-auto bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $stats['total_templates'] }}</span>
            </a>
            <a href="{{ route('admin.dashboard') }}#submissions" class="nav-item active flex items-center gap-3 px-6 py-3 text-sm" id="nav-submissions">
                <i class="fas fa-inbox w-5"></i>
                Submissions
                @if($stats['pending_reviews'] > 0)
                <span class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $stats['pending_reviews'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.dashboard') }}#users" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-users">
                <i class="fas fa-users w-5"></i>
                User Management
            </a>
            <a href="{{ route('admin.dashboard') }}#positions" class="nav-item flex items-center gap-3 px-6 py-3 text-sm" id="nav-positions">
                <i class="fas fa-id-badge w-5"></i>
                Positions
            </a>
        </nav>
        
        <div class="p-4 border-t border-gray-700 bg-slate-900/50">
            <p class="text-sm font-semibold truncate">{{ session('user')['name'] ?? 'Admin' }}</p>
            <p class="text-xs text-gray-400 truncate">{{ session('user')['email'] ?? '' }}</p>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Sticky Header -->
        <header class="glass-panel sticky top-0 z-10 px-8 py-4 flex justify-between items-center bg-white border-b border-slate-200">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}#submissions" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 transition flex items-center justify-center text-slate-600 hover:text-slate-900" title="Back to Submissions">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Submission Inspection</h2>
                    <p class="text-sm text-slate-500">Review employee spreadsheet entries and choose status</p>
                </div>
            </div>
            
            <a href="{{ route('admin.dashboard') }}#submissions" class="text-sm text-slate-600 hover:text-blue-600 font-semibold transition">
                <i class="fas fa-list mr-1"></i> Submission Queue
            </a>
        </header>

        <!-- Main Body (Scrollable) -->
        <div class="flex-1 overflow-auto p-8">
            @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg flex items-center gap-2 shadow-sm">
                <i class="fas fa-exclamation-circle"></i>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Info and Evaluation Panel -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Submission Metadata Card -->
                    <div class="glass-panel rounded-2xl p-6">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Submission Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Employee</label>
                                <p class="text-sm font-bold text-slate-800">{{ $submission->user?->full_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-slate-500">ID: {{ $submission->user?->employee_id ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Position & Office</label>
                                <p class="text-xs font-semibold text-slate-700">{{ $submission->user?->jobPosition?->name ?? 'No position' }}</p>
                                <p class="text-xs text-slate-500">{{ $submission->user?->office ?? $submission->user?->department ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">IPCRF Template</label>
                                <p class="text-xs font-semibold text-slate-700">{{ $submission->template?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $submission->template?->semester ?? 'N/A' }} Semester</p>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Status</label>
                                @php
                                    $statusClasses = [
                                        'draft'        => 'bg-slate-100 text-slate-600',
                                        'submitted'    => 'bg-blue-100 text-blue-700 border border-blue-200',
                                        'poo_approved' => 'bg-sky-100 text-sky-700 border border-sky-200',
                                        'under_review' => 'bg-orange-100 text-orange-700 border border-orange-200',
                                        'approved'     => 'bg-green-100 text-green-700 border border-green-200',
                                        'rejected'     => 'bg-red-100 text-red-700 border border-red-200'
                                    ];
                                    $class = $statusClasses[$submission->status] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <span class="status-badge {{ $class }} inline-block px-3 py-1 font-semibold text-xs">{{ $submission->statusLabel() }}</span>
                            </div>

                            @if($submission->submitted_at)
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Submitted At</label>
                                <p class="text-xs text-slate-600">{{ $submission->submitted_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @endif

                            @if($submission->status === 'approved' || $submission->status === 'rejected')
                            <div class="pt-4 border-t border-slate-100 space-y-3">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase">Reviewed By</label>
                                    <p class="text-xs font-semibold text-slate-700">{{ $submission->reviewer?->name ?? 'System' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $submission->reviewed_at ? $submission->reviewed_at->format('M d, Y h:i A') : '' }}</p>
                                </div>
                                @if($submission->admin_remarks)
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase">Remarks/Feedback</label>
                                    <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200 leading-relaxed">{{ $submission->admin_remarks }}</p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Evaluation Actions Panel (Only if Pending/Under Review) -->
                    @if(in_array($submission->status, ['poo_approved', 'under_review']))
                    <div class="glass-panel rounded-2xl p-6 border-t-4 border-blue-500 shadow-md">
                        <h3 class="font-bold text-slate-800 text-sm mb-3">Evaluation Control</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="admin-remarks" class="block text-xs font-bold text-slate-500 mb-1 uppercase">Review Feedback</label>
                                <textarea id="admin-remarks" rows="5" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none" placeholder="Provide correction notes or reasons if rejecting..."></textarea>
                            </div>

                            <div class="flex flex-col gap-2 pt-2">
                                <form id="approve-form" action="{{ route('admin.submissions.approve', $submission->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="remarks" id="approve-remarks-input">
                                    <button type="submit" onclick="document.getElementById('approve-remarks-input').value = document.getElementById('admin-remarks').value" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl font-bold text-xs shadow-sm transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fas fa-check"></i> Approve Submission
                                    </button>
                                </form>

                                <form id="reject-form" action="{{ route('admin.submissions.reject', $submission->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="remarks" id="reject-remarks-input">
                                    <button type="submit" onclick="
                                        const rem = document.getElementById('admin-remarks').value.trim();
                                        if(!rem) { alert('Please write a feedback remark before rejecting.'); return false; }
                                        document.getElementById('reject-remarks-input').value = rem;
                                    " class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl font-bold text-xs shadow-sm transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fas fa-times"></i> Reject & Return
                                    </button>
                                </form>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-normal">Approving seals this IPCRF. Rejecting will send it back to the employee's dashboard as a Draft with your feedback remarks.</p>
                        </div>
                    </div>
                    @endif

                    @if($submission->status === 'approved')
                    <div class="glass-panel rounded-2xl p-6 border-l-4 border-green-500 shadow-md text-center">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-3 block"></i>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Form Approved</h4>
                        <p class="text-xs text-slate-500 mb-4">Official report downloads are available for this submission.</p>
                        <a href="{{ route('admin.submissions.download', $submission->id) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold text-xs shadow-sm transition">
                            <i class="fas fa-file-excel"></i> Download Excel (XLSX)
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Spreadsheet Grid -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="glass-panel rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                                <i class="fas fa-table text-indigo-500"></i>
                                Spreadsheet Form Viewer
                            </h3>
                            <span class="text-xs text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">Read-Only Grid</span>
                        </div>
                        
                        <!-- Scrollable Table Container -->
                        <div class="ipcrf-grid-wrap" data-submission-id="{{ $submission->id }}">
                            {!! $htmlTable !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const submissionStatus = '{{ $submission->status }}';
    const isAdminPooAdmin = {{ $isAdminPooAdmin ? 'true' : 'false' }};

    document.addEventListener('DOMContentLoaded', function() {
        initAdminEditableGrid('.ipcrf-grid-wrap');
    });

    function initAdminEditableGrid(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        if (container.dataset.initialized) return;
        container.dataset.initialized = 'true';

        // Delegate clicks on signature upload container
        container.addEventListener('click', function(e) {
            const wrapper = e.target.closest('.admin-sig-wrapper');
            if (!wrapper) return;
            
            if (e.target.classList.contains('admin-sig-file-input')) {
                return;
            }

            // Check if RPMO admin trying to edit approving authority field
            const fieldType = wrapper.getAttribute('data-field-type');
            if (!isAdminPooAdmin && fieldType && fieldType.includes('autofill_approving_authority')) {
                return; // Don't allow RPMO admins to click
            }

            // Check if this is a poo_approved submission and approving authority field
            if (submissionStatus === 'poo_approved' && fieldType && fieldType.includes('autofill_approving_authority')) {
                return; // Don't allow editing
            }
            
            const fileInput = wrapper.querySelector('.admin-sig-file-input');
            if (fileInput) {
                fileInput.click();
            }
        });

        container.addEventListener('change', async function(e) {
            const target = e.target;

            // Handle file upload for admin reviewer signature
            if (target.classList.contains('admin-sig-file-input')) {
                const wrapper = target.closest('.admin-sig-wrapper');
                if (!wrapper) return;

                // Check if RPMO admin trying to edit approving authority field
                const fieldType = wrapper.getAttribute('data-field-type');
                if (!isAdminPooAdmin && fieldType && fieldType.includes('autofill_approving_authority')) {
                    alert('Only POO admins can edit approving authority fields');
                    target.value = '';
                    return;
                }

                // Check if submission is poo_approved and this is an approving authority field
                if (submissionStatus === 'poo_approved' && fieldType && fieldType.includes('autofill_approving_authority')) {
                    alert('Cannot edit approving authority fields when submission is already approved by POO');
                    target.value = '';
                    return;
                }
                
                const file = target.files[0];
                if (!file) return;

                const submissionId = container.getAttribute('data-submission-id');
                const fieldId = wrapper.getAttribute('data-field-id');
                const cellRef = wrapper.getAttribute('data-cell-ref');
                
                if (!submissionId || !fieldId) return;

                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');

                // Visual feedback - show upload indicator
                const originalContentHtml = wrapper.innerHTML;
                wrapper.innerHTML = '<span class="text-[10px] font-semibold text-indigo-600"><i class="fas fa-spinner fa-spin mr-1"></i>Uploading...</span>';

                try {
                    const uploadRes = await fetch(`/my/submissions/${submissionId}/upload-picture/${fieldId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    const uploadResult = await uploadRes.json();
                    if (!uploadResult.success) {
                        alert('Upload failed: ' + (uploadResult.message || 'Unknown error'));
                        wrapper.innerHTML = originalContentHtml;
                        return;
                    }

                    // Save the uploaded picture URL and location as a JSON answer
                    const value = JSON.stringify({
                        url: uploadResult.url,
                        cell_ref: cellRef,
                        offsetX: 0,
                        offsetY: 0,
                        width: 120,
                        height: 60
                    });

                    const saveRes = await fetch(`/admin/submissions/${submissionId}/save-answers`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            answers: {
                                [fieldId]: value
                            }
                        })
                    });

                    const saveResult = await saveRes.json();
                    if (saveResult.success) {
                        wrapper.innerHTML = '';
                        
                        const img = document.createElement('img');
                        img.src = uploadResult.url;
                        img.style.maxHeight = '40px';
                        img.style.maxWidth = '100%';
                        img.style.display = 'block';
                        img.style.margin = '0 auto';
                        wrapper.appendChild(img);

                        const replaceSpan = document.createElement('span');
                        replaceSpan.className = 'absolute top-0 right-0 bg-gray-800/80 text-white text-[8px] px-1 rounded hover:bg-black pointer-events-none';
                        replaceSpan.textContent = 'Replace';
                        wrapper.appendChild(replaceSpan);

                        const newFileInput = document.createElement('input');
                        newFileInput.type = 'file';
                        newFileInput.className = 'admin-sig-file-input hidden';
                        newFileInput.accept = '.png';
                        wrapper.appendChild(newFileInput);
                    } else {
                        alert('Failed to save signature: ' + (saveResult.message || 'Unknown error'));
                        wrapper.innerHTML = originalContentHtml;
                    }
                } catch (err) {
                    console.error(err);
                    alert('Upload/Save failed due to network error.');
                    wrapper.innerHTML = originalContentHtml;
                }
                return;
            }

            // Existing logic for standard text / position change
            if (!target.classList.contains('admin-form-input')) return;

            const submissionId = container.getAttribute('data-submission-id');
            if (!submissionId) return;

            const fieldId = target.getAttribute('data-field-id');
            const fieldType = target.getAttribute('data-field-type');
            const value = target.value;

            // Check if RPMO admin trying to edit approving authority field
            if (!isAdminPooAdmin && fieldType && fieldType.includes('autofill_approving_authority')) {
                alert('Only POO admins can edit approving authority fields');
                target.style.opacity = '1';
                location.reload(); // Reload to restore original state
                return;
            }

            // Check if submission is poo_approved and this is an approving authority field
            if (submissionStatus === 'poo_approved' && fieldType && fieldType.includes('autofill_approving_authority')) {
                alert('Cannot edit approving authority fields when submission is already approved by POO');
                target.style.opacity = '1';
                location.reload(); // Reload to restore original state
                return;
            }

            // Visual feedback - saving state
            target.style.opacity = '0.6';
            
            try {
                const response = await fetch(`/admin/submissions/${submissionId}/save-answers`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        answers: {
                            [fieldId]: value
                        }
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    // Flash green background briefly for success
                    const originalBg = target.style.backgroundColor;
                    target.style.backgroundColor = '#d1fae5';
                    setTimeout(() => {
                        target.style.backgroundColor = originalBg;
                        target.style.opacity = '1';
                    }, 500);
                } else {
                    target.style.backgroundColor = '#fee2e2';
                    alert('Failed to save: ' + (result.message || 'Unknown error'));
                    target.style.opacity = '1';
                }
            } catch (err) {
                console.error(err);
                target.style.backgroundColor = '#fee2e2';
                alert('Network error while saving changes.');
                target.style.opacity = '1';
            }
        });
    }
</script>
@endsection
