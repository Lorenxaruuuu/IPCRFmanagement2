@extends('app')

@section('header', 'Encoder Dashboard')

@section('content')
<div x-data="{ 
    showDetailsModal: false, 
    selectedUpload: { name: '', province: '', municipality: '', status: '', date: '', evaluated_file: '', scanned_file: '', gdrive_file_id: 'N/A', gdrive_link: '' }
}" class="space-y-8">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full
                    {{ $growthPercentage >= 0
                        ? 'bg-blue-50 text-blue-700'
                        : 'bg-red-50 text-red-700' }}">

                    {{ $growthPercentage >= 0 ? '+' : '' }}
                    {{ round($growthPercentage, 1) }}%
                </span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $totalUploaded ?? 0 }}</h3>
            <p class="text-slate-500 text-sm">Total IPCRF Uploaded</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium bg-orange-50 text-orange-700 px-2 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $pendingReview ?? 0 }}</h3>
            <p class="text-slate-500 text-sm">Pending Review</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full">Today</span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $completedToday ?? 0 }}</h3>
            <p class="text-slate-500 text-sm">Completed Today</p>
        </div>
    </div>

    <!-- Action Section -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-800">Recent Uploads</h2>
        <a href="{{ route('upload.create') }}"
        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium flex items-center gap-2 shadow-lg shadow-blue-600/30 transition-all duration-200
        hover:bg-white hover:text-blue-600 hover:shadow-5xl hover:-translate-y-0.5 active:scale-95">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Upload Evaluated IPCRF
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex gap-4">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                <input
                    type="text"
                    placeholder="Search by name, province, or ID..."
                    class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                />
            </div>
            <button class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Filter
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium">
                    <tr>
                        <th class="px-6 py-4">Employee Name</th>
                        <th class="px-6 py-4">Location</th>
                        <th class="px-6 py-4">Date Uploaded</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentUploads ?? [] as $upload)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $upload->name }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $upload->municipality }}, {{ $upload->province }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $upload->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium 
                                {{ $upload->status === 'Pending' 
                                    ? 'bg-orange-50 text-orange-700 border border-orange-100' 
                                    : 'bg-green-50 text-green-700 border border-green-100' }}">
                                
                                <span class="w-1.5 h-1.5 rounded-full 
                                    {{ $upload->status === 'Pending' ? 'bg-orange-600' : 'bg-green-600' }}">
                                </span>
                                {{ $upload->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click.stop="
                                selectedUpload = {
                                    name: '{{ addslashes($upload->name) }}',
                                    province: '{{ addslashes($upload->province) }}',
                                    municipality: '{{ addslashes($upload->municipality) }}',
                                    status: '{{ addslashes($upload->status) }}',
                                    date: '{{ $upload->created_at->format('M d, Y H:i A') }}',
                                    evaluated_file: '{{ addslashes($upload->evaluated_file_path ?? 'Pending') }}',
                                    scanned_file: '{{ addslashes($upload->scanned_file_path ?? 'Pending') }}',
                                    gdrive_file_id: '{{ addslashes($upload->google_drive_file_id ?? 'N/A') }}',
                                    gdrive_link: '{{ addslashes($upload->google_drive_link ?? '') }}'
                                };
                                showDetailsModal = true;
                            " class="text-blue-600 hover:text-blue-800 font-semibold hover:underline bg-transparent border-0 cursor-pointer">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            No uploads found. Start by uploading a new IPCRF.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- DETAILS MODAL -->
    <div x-show="showDetailsModal"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
         style="display: none;"
         @click="showDetailsModal = false">

        <div class="bg-white rounded-2xl shadow-2xl p-8 w-[500px] text-left border border-slate-100 relative" @click.stop>
            <button @click="showDetailsModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition cursor-pointer">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-6">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>

            <h3 class="text-2xl font-bold mb-1 text-slate-800">IPCRF Upload Details</h3>
            <p class="text-sm text-slate-500 mb-6">Full metadata and transfer status of this record.</p>

            <div class="space-y-4 text-sm text-slate-700">
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Employee Name</span>
                    <span class="font-medium text-slate-800" x-text="selectedUpload.name"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Province</span>
                    <span class="font-medium text-slate-800" x-text="selectedUpload.province"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Municipality</span>
                    <span class="font-medium text-slate-800" x-text="selectedUpload.municipality"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Date Uploaded</span>
                    <span class="font-medium text-slate-800" x-text="selectedUpload.date"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Evaluated File</span>
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" x-text="selectedUpload.evaluated_file"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Scanned File</span>
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" x-text="selectedUpload.scanned_file"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Status</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border"
                          :class="{
                              'bg-green-50 text-green-700 border-green-100': selectedUpload.status === 'Sent to Zapier' || selectedUpload.status === 'Completed' || selectedUpload.status === 'Success',
                              'bg-orange-50 text-orange-700 border-orange-100': selectedUpload.status === 'Pending' || selectedUpload.status === 'Pending Review',
                              'bg-blue-50 text-blue-700 border-blue-100': selectedUpload.status !== 'Sent to Zapier' && selectedUpload.status !== 'Completed' && selectedUpload.status !== 'Success' && selectedUpload.status !== 'Pending' && selectedUpload.status !== 'Pending Review'
                          }">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="{
                                  'bg-green-600': selectedUpload.status === 'Sent to Zapier' || selectedUpload.status === 'Completed' || selectedUpload.status === 'Success',
                                  'bg-orange-600': selectedUpload.status === 'Pending' || selectedUpload.status === 'Pending Review',
                                  'bg-blue-600': selectedUpload.status !== 'Sent to Zapier' && selectedUpload.status !== 'Completed' && selectedUpload.status !== 'Success' && selectedUpload.status !== 'Pending' && selectedUpload.status !== 'Pending Review'
                              }">
                        </span>
                        <span x-text="selectedUpload.status"></span>
                    </span>
                </div>
                
                <div x-show="selectedUpload.gdrive_file_id && selectedUpload.gdrive_file_id !== 'N/A'" class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Google Drive File ID</span>
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" x-text="selectedUpload.gdrive_file_id"></span>
                </div>
            </div>

            <div class="flex gap-3 justify-end mt-8">
                <button type="button" @click="showDetailsModal = false"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-semibold text-sm cursor-pointer">
                    Close Details
                </button>
                
                <a x-show="selectedUpload.gdrive_link" :href="selectedUpload.gdrive_link" target="_blank"
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Open in Drive
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
