@extends('app')

@section('header', 'Uploaded IPCRF List')

@section('content')
<div x-data="{
    activeDropdown: null,
    showDetailsModal: false,
    selectedRecord: { id: '', name: '', province: '', municipality: '', status: '', date: '', evaluated_file: '', scanned_file: '', gdrive_file_id: '', gdrive_link: '' }
}" class="space-y-6" @click="activeDropdown = null">
    <div class="flex justify-between items-center">
        <h3 class="text-slate-800 font-bold text-lg">All Submissions</h3>
        <a href="{{ route('upload.create') }}" class="bg-blue-600 text-white hover:bg-white hover:text-blue-600 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i> Add New
        </a>    
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex gap-4">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                <input type="text" id="searchBox" placeholder="Search by name, ID, municipality, province..." 
                    class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                />
            </div>
            <div class="flex gap-2">
                <select class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 bg-white focus:outline-none focus:border-blue-500">
                    <option value="">All Provinces</option>
                    <option value="Davao de Oro">Davao de Oro</option>
                    <option value="Davao del Norte">Davao del Norte</option>
                    <option value="Davao del Sur">Davao del Sur</option>
                    <option value="Davao Occidental">Davao Occidental</option>
                    <option value="Davao Oriental">Davao Oriental</option>
                </select>
                <button id="exportBtn" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Export
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-medium">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Employee Name</th>
                        <th class="px-6 py-4">Location</th>
                        <th class="px-6 py-4">Date Uploaded</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ipcrfs as $ipcrf)
                    <tr class="hover:bg-slate-50 transition-colors table-row" 
                        data-id="{{ $ipcrf->id }}"
                        data-province="{{ $ipcrf->province }}" 
                        data-name="{{ $ipcrf->name }}" 
                        data-municipality="{{ $ipcrf->municipality }}"
                        data-status="{{ $ipcrf->status }}">
                        <td class="px-6 py-4 text-slate-500">#{{ str_pad($ipcrf->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $ipcrf->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $ipcrf->municipality }}, {{ $ipcrf->province }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $ipcrf->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium 
                            {{ $ipcrf->status === 'Pending' 
                                ? 'bg-orange-50 text-orange-700 border border-orange-100' 
                                : 'bg-green-50 text-green-700 border border-green-100' }}">
                            
                            <span class="w-1.5 h-1.5 rounded-full 
                                {{ $ipcrf->status === 'Pending' ? 'bg-orange-600' : 'bg-green-600' }}">
                            </span>
                            {{ $ipcrf->status }}
                        </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2.5">
                                <button @click="
                                    selectedRecord = {
                                        id: '{{ str_pad($ipcrf->id, 5, '0', STR_PAD_LEFT) }}',
                                        name: '{{ addslashes($ipcrf->name) }}',
                                        province: '{{ addslashes($ipcrf->province) }}',
                                        municipality: '{{ addslashes($ipcrf->municipality) }}',
                                        status: '{{ addslashes($ipcrf->status) }}',
                                        date: '{{ $ipcrf->created_at->format('M d, Y h:i A') }}',
                                        evaluated_file: '{{ addslashes($ipcrf->evaluated_file_path ?? 'Pending') }}',
                                        scanned_file: '{{ addslashes($ipcrf->scanned_file_path ?? 'Pending') }}',
                                        gdrive_file_id: '{{ addslashes($ipcrf->google_drive_file_id ?? 'N/A') }}',
                                        gdrive_link: '{{ addslashes($ipcrf->google_drive_link ?? '') }}'
                                    };
                                    showDetailsModal = true;
                                " class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 transition rounded-lg text-xs font-semibold cursor-pointer">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    View Details
                                </button>

                                <a href="https://hooks.zapier.com/hooks/catch/26959129/upv3mc5/" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition rounded-lg text-xs font-semibold cursor-pointer">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    Download Scanned
                                </a>

                                @if($ipcrf->google_drive_link)
                                <a href="{{ $ipcrf->google_drive_link }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition rounded-lg text-xs font-semibold cursor-pointer"
                                   title="Open in Google Drive">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    GDrive
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6 text-slate-400"></i>
                                </div>
                                <p>No records found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t border-slate-200">
            {{ $ipcrfs->links() }}
        </div>
    </div>

    <!-- DETAILS MODAL -->
    <div x-show="showDetailsModal"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
         style="display: none;"
         @click="showDetailsModal = false">

        <div class="bg-white rounded-2xl shadow-2xl p-8 w-[500px] text-left border border-slate-100 relative animate-fade-in" @click.stop>
            <button @click="showDetailsModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition cursor-pointer">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-6">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>

            <h3 class="text-2xl font-bold mb-1 text-slate-800">IPCRF Submission Details</h3>
            <p class="text-sm text-slate-500 mb-6">Full metadata and transfer status of this record.</p>

            <div class="space-y-4 text-sm text-slate-700">
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Record ID</span>
                    <span class="font-medium text-slate-800" x-text="'#' + selectedRecord.id"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Employee Name</span>
                    <span class="font-medium text-slate-800" x-text="selectedRecord.name"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Province</span>
                    <span class="font-medium text-slate-800" x-text="selectedRecord.province"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Municipality</span>
                    <span class="font-medium text-slate-800" x-text="selectedRecord.municipality"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Date Uploaded</span>
                    <span class="font-medium text-slate-800" x-text="selectedRecord.date"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Evaluated File</span>
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" x-text="selectedRecord.evaluated_file"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Scanned File</span>
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" x-text="selectedRecord.scanned_file"></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Status</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border"
                          :class="{
                              'bg-green-50 text-green-700 border-green-100': selectedRecord.status === 'Sent to Zapier' || selectedRecord.status === 'Completed' || selectedRecord.status === 'Success',
                              'bg-orange-50 text-orange-700 border-orange-100': selectedRecord.status === 'Pending' || selectedRecord.status === 'Pending Review',
                              'bg-blue-50 text-blue-700 border-blue-100': selectedRecord.status !== 'Sent to Zapier' && selectedRecord.status !== 'Completed' && selectedRecord.status !== 'Success' && selectedRecord.status !== 'Pending' && selectedRecord.status !== 'Pending Review'
                          }">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="{
                                  'bg-green-600': selectedRecord.status === 'Sent to Zapier' || selectedRecord.status === 'Completed' || selectedRecord.status === 'Success',
                                  'bg-orange-600': selectedRecord.status === 'Pending' || selectedRecord.status === 'Pending Review',
                                  'bg-blue-600': selectedRecord.status !== 'Sent to Zapier' && selectedRecord.status !== 'Completed' && selectedRecord.status !== 'Success' && selectedRecord.status !== 'Pending' && selectedRecord.status !== 'Pending Review'
                              }">
                        </span>
                        <span x-text="selectedRecord.status"></span>
                    </span>
                </div>
                
                <div x-show="selectedRecord.gdrive_file_id && selectedRecord.gdrive_file_id !== 'N/A'" class="flex justify-between py-2.5 border-b border-slate-100">
                    <span class="font-semibold text-slate-500 uppercase tracking-wider text-[11px]">Google Drive File ID</span>
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" x-text="selectedRecord.gdrive_file_id"></span>
                </div>
            </div>

            <div class="flex gap-3 justify-end mt-8">
                <button type="button" @click="showDetailsModal = false"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-semibold text-sm cursor-pointer">
                    Close Details
                </button>
                
                <a x-show="selectedRecord.gdrive_link" :href="selectedRecord.gdrive_link" target="_blank"
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Open in Drive
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBox');
    const provinceFilter = document.querySelector('select');
    const exportButton = document.getElementById('exportBtn');
    const tableRows = document.querySelectorAll('.table-row');
    let dynamicEmptyState = null;

    // Search functionality
    function filterRecords() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedProvince = provinceFilter.value;
        let visibleCount = 0;

        tableRows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const municipality = row.dataset.municipality.toLowerCase();
            const province = row.dataset.province;
            const status = row.dataset.status.toLowerCase();
            const id = row.dataset.id || '';
            const paddedId = id.padStart(5, '0');

            const matchesSearch = !searchTerm || 
                name.includes(searchTerm) || 
                municipality.includes(searchTerm) || 
                province.toLowerCase().includes(searchTerm) ||
                status.includes(searchTerm) ||
                id.includes(searchTerm) ||
                paddedId.includes(searchTerm) ||
                `#${paddedId}`.includes(searchTerm);

            const matchesProvince = !selectedProvince || province === selectedProvince;

            if (matchesSearch && matchesProvince) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide dynamic empty state
        const tbody = document.querySelector('tbody');
        
        // Remove any existing dynamic empty state
        if (dynamicEmptyState) {
            dynamicEmptyState.remove();
            dynamicEmptyState = null;
        }

        // Show empty state only if there are dynamic rows hidden and no visible results
        if (visibleCount === 0 && tableRows.length > 0) {
            const newEmptyState = document.createElement('tr');
            newEmptyState.className = 'dynamic-empty-state';
            newEmptyState.innerHTML = `
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                            <i data-lucide="search" class="w-6 h-6 text-slate-400"></i>
                        </div>
                        <p>No records match your search</p>
                    </div>
                </td>
            `;
            tbody.appendChild(newEmptyState);
            dynamicEmptyState = newEmptyState;
            lucide.createIcons();
        }
    }

    // Event listeners
    if (searchInput) searchInput.addEventListener('input', filterRecords);
    if (provinceFilter) provinceFilter.addEventListener('change', filterRecords);

    // Export functionality
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
            
            if (visibleRows.length === 0) {
                alert('No records to export');
                return;
            }

            // Create CSV content
            let csvContent = 'ID,Employee Name,Location,Date Uploaded,Status\n';
            
            visibleRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const id = cells[0].textContent.trim();
                const name = cells[1].textContent.trim();
                const location = cells[2].textContent.trim();
                const date = cells[3].textContent.trim();
                const status = cells[4].textContent.trim();
                
                csvContent += `"${id}","${name}","${location}","${date}","${status}"\n`;
            });

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'ipcrf_records.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    // Initialize Lucide icons
    lucide.createIcons();
});
</script>
@endpush
