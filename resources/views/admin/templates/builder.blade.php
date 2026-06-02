@extends('admin.layouts.admin')
@section('title', 'Template Builder — ' . $template->name)

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
*{box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#0f172a;color:#f1f5f9;margin:0;overflow:hidden;}
.builder-layout{display:flex;flex-direction:column;height:100vh;}
.builder-header{background:#1e293b;border-bottom:1px solid rgba(255,255,255,.08);padding:0 20px;height:56px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.builder-body{display:flex;flex:1;overflow:hidden;}
.sheet-area{flex:1;overflow:auto;background:#fff;position:relative;}
.side-panel{width:280px;flex-shrink:0;background:#1e293b;border-left:1px solid rgba(255,255,255,.08);display:flex;flex-direction:column;overflow:hidden;}
.sp-header{padding:16px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;}
.sp-body{flex:1;overflow-y:auto;padding:16px;}
.sp-footer{padding:12px 16px;border-top:1px solid rgba(255,255,255,.06);flex-shrink:0;}

/* Spreadsheet table */
.ipcrf-preview-table{border-collapse:collapse;min-width:100%;font-size:11px;color:#1e293b;}
.ipcrf-preview-table td{border:1px solid #cbd5e1;padding:2px 4px;vertical-align:middle;cursor:pointer;position:relative;overflow:hidden;white-space:nowrap;}
.ipcrf-preview-table td:hover{background:rgba(99,102,241,.08) !important;outline:2px solid rgba(99,102,241,.4);z-index:1;}
.ipcrf-preview-table td.selected-cell{outline:2px solid #f59e0b !important;background:rgba(245,158,11,.06) !important;z-index:2;}
.ipcrf-preview-table td.mapped-cell{outline:2px solid rgba(99,102,241,.5);background:rgba(99,102,241,.04);}
.field-badge{display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;padding:1px 5px;border-radius:3px;max-width:100%;overflow:hidden;text-overflow:ellipsis;}
.field-badge.autofill_name,.field-badge.autofill_position,.field-badge.autofill_department,.field-badge.autofill_date{background:#d1fae5;color:#065f46;}
.field-badge.text,.field-badge.number,.field-badge.textarea{background:#dbeafe;color:#1e40af;}
.field-badge.rating{background:#fef3c7;color:#92400e;}
.field-badge.dropdown{background:#fde68a;color:#78350f;}
.field-badge.signature{background:#fce7f3;color:#9d174d;}
.field-badge.readonly{background:#f1f5f9;color:#475569;}
.field-badge.picture{background:#e0f2fe;color:#0369a1;}

/* Side panel form controls */
.sp-label{font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.06em;}
.sp-input{width:100%;background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 10px;font-size:12px;color:#f1f5f9;outline:none;}
.sp-input:focus{border-color:#6366f1;}
.ft-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
.ft-option{display:flex;align-items:center;gap:6px;padding:7px 9px;border-radius:7px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);cursor:pointer;font-size:11px;font-weight:500;color:#94a3b8;transition:all .15s;}
.ft-option:hover{border-color:rgba(99,102,241,.4);color:#a5b4fc;background:rgba(99,102,241,.08);}
.ft-option.active{border-color:#6366f1;background:rgba(99,102,241,.18);color:#c7d2fe;}
.ft-option i{width:14px;text-align:center;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;}
.btn-primary:hover{opacity:.9;}
.btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.1);}
.btn-ghost:hover{background:rgba(255,255,255,.12);color:#f1f5f9;}
.btn-danger{background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);}
.btn-sm{padding:4px 10px;font-size:11px;}

/* Position assignment */
.pos-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500;border:1px solid rgba(99,102,241,.3);background:rgba(99,102,241,.1);color:#a5b4fc;cursor:pointer;transition:all .15s;}
.pos-tag.selected{background:rgba(99,102,241,.3);border-color:#6366f1;}

/* Field list */
.field-item{display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:rgba(15,23,42,.5);margin-bottom:6px;}
.field-item-ref{font-size:11px;font-weight:700;color:#818cf8;min-width:30px;}
.field-item-type{font-size:10px;color:#64748b;}
.field-item-label{font-size:12px;color:#f1f5f9;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

/* Tabs */
.tab-row{display:flex;border-bottom:1px solid rgba(255,255,255,.06);background:#1e293b;flex-shrink:0;}
.tab-btn{padding:10px 16px;font-size:12px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;border-bottom:2px solid transparent;transition:all .2s;}
.tab-btn.active{color:#a5b4fc;border-bottom-color:#6366f1;}

/* Toast */
.toast{position:fixed;bottom:20px;right:20px;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 16px;font-size:13px;color:#f1f5f9;z-index:9999;transform:translateY(20px);opacity:0;transition:all .3s;min-width:260px;display:flex;align-items:center;gap:8px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast-success{border-left:3px solid #10b981;}
/* Spreadsheet Table Headers (Row/Col Indicators) */
.ipcrf-hdr-corner,
.ipcrf-hdr-col,
.ipcrf-hdr-row {
    background: #f8fafc !important;
    color: #475569 !important;
    font-weight: 700 !important;
    font-size: 10px !important;
    text-align: center !important;
    border: 1px solid #cbd5e1 !important;
    user-select: none !important;
    position: relative !important;
}
.ipcrf-hdr-corner {
    width: 40px;
    height: 24px;
}
.ipcrf-hdr-col {
    height: 24px;
    vertical-align: middle !important;
}
.ipcrf-hdr-row {
    width: 40px;
    vertical-align: middle !important;
}

/* Resizers */
.col-resizer {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 6px;
    cursor: col-resize;
    z-index: 10;
    background: transparent;
    transition: background 0.15s;
}
.col-resizer:hover, .col-resizer.dragging {
    background: #6366f1;
}
.row-resizer {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 6px;
    cursor: row-resize;
    z-index: 10;
    background: transparent;
    transition: background 0.15s;
}
.row-resizer:hover, .row-resizer.dragging {
    background: #6366f1;
}

/* Borderless inputs to look exactly like Google Sheets cells */
.ipcrf-preview-table input,
.ipcrf-preview-table textarea,
.ipcrf-preview-table select {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
    background: transparent !important;
    outline: none !important;
    font-family: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    color: inherit !important;
    text-align: inherit !important;
    padding: 2px 4px !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}
.ipcrf-preview-table input:focus,
.ipcrf-preview-table textarea:focus,
.ipcrf-preview-table select:focus {
    background: rgba(99, 102, 241, 0.05) !important;
    box-shadow: inset 0 0 0 2px #6366f1 !important;
}
</style>
@endpush

@section('content')
<div class="builder-layout" x-data="builderApp()" x-init="init()">

    {{-- ── HEADER ── --}}
    <div class="builder-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('admin.dashboard') }}" style="color:#64748b;font-size:18px;text-decoration:none;"><i class="fas fa-arrow-left"></i></a>
            <div>
                <p style="font-size:14px;font-weight:700;color:#f1f5f9;margin:0;">{{ $template->name }}</p>
                <p style="font-size:11px;color:#64748b;margin:0;">Template Builder — click any cell to assign a field type</p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:11px;color:#64748b;" x-text="mappedFields.length + ' fields mapped'"></span>
            <button class="btn btn-ghost btn-sm" @click="tab='positions'">
                <i class="fas fa-id-badge"></i> Assign Positions
            </button>
            <button class="btn btn-success" @click="saveAll()">
                <span x-show="!saving"><i class="fas fa-save"></i> Save All</span>
                <span x-show="saving"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
            </button>
        </div>
    </div>

    {{-- ── TAB ROW ── --}}
    <div class="tab-row">
        <button class="tab-btn" :class="{active:tab==='mapper'}" @click="tab='mapper'"><i class="fas fa-magic" style="margin-right:5px;"></i>Field Mapper</button>
        <button class="tab-btn" :class="{active:tab==='fields'}" @click="tab='fields'"><i class="fas fa-list" style="margin-right:5px;"></i>Mapped Fields (<span x-text="mappedFields.length"></span>)</button>
        <button class="tab-btn" :class="{active:tab==='positions'}" @click="tab='positions'"><i class="fas fa-id-badge" style="margin-right:5px;"></i>Position Access</button>
    </div>

    {{-- ── BODY ── --}}
    <div class="builder-body">

        {{-- Spreadsheet Preview --}}
        <div class="sheet-area" id="sheet-area">
            {!! $htmlTable !!}
        </div>

        {{-- Side Panel --}}
        <div class="side-panel">

            {{-- MAPPER TAB --}}
            <template x-if="tab==='mapper'">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="sp-header">
                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0 0 4px;">
                            <template x-if="selectedCell">
                                Selected: <span style="color:#f59e0b;" x-text="selectedCell"></span>
                            </template>
                            <template x-if="!selectedCell">
                                <span style="color:#475569;">No cell selected</span>
                            </template>
                        </p>
                        <p style="font-size:11px;color:#64748b;margin:0;">Click a cell in the spreadsheet to configure it</p>
                    </div>

                    <div class="sp-body" x-show="selectedCell">
                        <div style="margin-bottom:14px;">
                            <label class="sp-label">Field Type</label>
                            <div class="ft-grid">
                                @php
                                $fieldTypes = [
                                    'autofill_name'       => ['fa-user',        'Employee Name'],
                                    'autofill_position'   => ['fa-briefcase',   'Position'],
                                    'autofill_department' => ['fa-building',    'Department'],
                                    'autofill_date'       => ['fa-calendar',    'Date Signed'],
                                    'text'                => ['fa-font',        'Text Input'],
                                    'number'              => ['fa-hashtag',     'Number'],
                                    'textarea'            => ['fa-align-left',  'Text Area'],
                                    'rating'              => ['fa-star',        'Rating'],
                                    'dropdown'            => ['fa-chevron-down','Dropdown'],
                                    'signature'           => ['fa-signature',   'Signature'],
                                    'readonly'            => ['fa-lock',        'Read-Only'],
                                    'picture'             => ['fa-image',       'Add Picture'],
                                ];
                                @endphp
                                @foreach($fieldTypes as $type => [$icon, $label])
                                <div class="ft-option" :class="{active: currentFieldType==='{{ $type }}'}" @click="{{ $type === 'picture' ? 'triggerAdminPictureUpload()' : "currentFieldType='" . $type . "'" }}">
                                    <i class="fas {{ $icon }}" style="font-size:11px;"></i>{{ $label }}
                                </div>
                                @endforeach
                            </div>
                            <input type="file" id="admin-picture-uploader" accept=".png,.jpg,.jpeg" style="display:none;" @change="handleAdminPictureSelected($event)">
                        </div>

                        <div style="margin-bottom:12px;">
                            <label class="sp-label">Field Label (optional)</label>
                            <input type="text" class="sp-input" placeholder="e.g., Employee Full Name" x-model="currentFieldLabel">
                        </div>

                        <div x-show="currentFieldType==='dropdown'" style="margin-bottom:12px;">
                            <label class="sp-label">Dropdown Options (one per line)</label>
                            <textarea class="sp-input" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3" x-model="currentDropdownOptions" style="resize:vertical;"></textarea>
                        </div>

                        <div style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" id="req-chk" x-model="currentRequired" style="accent-color:#6366f1;">
                            <label for="req-chk" style="font-size:12px;color:#94a3b8;cursor:pointer;">Required field</label>
                        </div>

                        <div style="display:flex;gap:8px;">
                            <button class="btn btn-primary" style="flex:1;justify-content:center;font-size:12px;" @click="assignField()">
                                <i class="fas fa-check"></i> Assign
                            </button>
                            <button class="btn btn-danger btn-sm" @click="removeField(selectedCell)" x-show="isCellMapped(selectedCell)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="sp-body" x-show="!selectedCell" style="display:flex;align-items:center;justify-content:center;height:100%;">
                        <div style="text-align:center;color:#475569;">
                            <i class="fas fa-mouse-pointer" style="font-size:32px;margin-bottom:12px;display:block;"></i>
                            <p style="font-size:13px;">Click any cell in the spreadsheet to configure it as a form field</p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- FIELDS LIST TAB --}}
            <template x-if="tab==='fields'">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="sp-header">
                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0;">Mapped Fields</p>
                        <p style="font-size:11px;color:#64748b;margin:0;" x-text="mappedFields.length + ' cells configured'"></p>
                    </div>
                    <div class="sp-body">
                        <template x-if="mappedFields.length===0">
                            <div style="text-align:center;color:#475569;padding:40px 0;">
                                <i class="fas fa-th" style="font-size:28px;display:block;margin-bottom:12px;"></i>
                                <p style="font-size:12px;">No fields mapped yet. Click cells in the spreadsheet.</p>
                            </div>
                        </template>
                        <template x-for="f in mappedFields" :key="f.cell_ref">
                            <div class="field-item" @click="selectCellFromField(f.cell_ref)">
                                <span class="field-item-ref" x-text="f.cell_ref"></span>
                                <div style="flex:1;min-width:0;">
                                    <div class="field-item-label" x-text="f.field_label || f.field_type"></div>
                                    <div class="field-item-type" x-text="fieldTypeLabel(f.field_type)"></div>
                                </div>
                                <button class="btn btn-danger btn-sm" style="padding:2px 6px;" @click.stop="removeField(f.cell_ref)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- POSITIONS TAB --}}
            <template x-if="tab==='positions'">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="sp-header">
                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0;">Position Access Control</p>
                        <p style="font-size:11px;color:#64748b;margin:0;">Which positions can access this template</p>
                    </div>
                    <div class="sp-body">
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 12px;">Select all positions that should have access to this IPCRF template:</p>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            @forelse($positions as $position)
                            <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.07);background:rgba(15,23,42,.5);cursor:pointer;transition:all .15s;"
                                :style="selectedPositions.includes({{ $position->id }}) ? 'border-color:#6366f1;background:rgba(99,102,241,.1);' : ''">
                                <input type="checkbox" :value="{{ $position->id }}" x-model="selectedPositions" style="accent-color:#6366f1;width:14px;height:14px;">
                                <div>
                                    <p style="font-size:13px;font-weight:600;color:#f1f5f9;margin:0;">{{ $position->name }}</p>
                                    @if($position->description)
                                    <p style="font-size:11px;color:#64748b;margin:2px 0 0;">{{ $position->description }}</p>
                                    @endif
                                </div>
                            </label>
                            @empty
                            <div style="text-align:center;color:#475569;padding:24px;">
                                <i class="fas fa-id-badge" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                                <p style="font-size:12px;">No positions configured yet.<br><a href="{{ route('admin.dashboard') }}" style="color:#6366f1;">Add positions first</a></p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="sp-footer">
                        <button class="btn btn-success" style="width:100%;justify-content:center;" @click="savePositions()">
                            <i class="fas fa-save"></i> Save Position Access
                        </button>
                    </div>
                </div>
            </template>

        </div>{{-- /side-panel --}}
    </div>{{-- /builder-body --}}

    {{-- Toast --}}
    <div class="toast" :class="toast.type ? 'toast-'+toast.type : ''" id="toast">
        <i :class="toast.type==='success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"
           :style="'color:'+(toast.type==='success'?'#10b981':'#ef4444')"></i>
        <span x-text="toast.message"></span>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF        = '{{ csrf_token() }}';
const TEMPLATE_ID = {{ $template->id }};

// Pre-load existing mappings from server
const INITIAL_FIELDS = @json($mappedFields);
const ASSIGNED_POSITIONS = @json($template->positions->pluck('id')->toArray());

function builderApp() {
    return {
        tab: 'mapper',
        saving: false,
        toast: { message: '', type: '' },

        // Cell selection
        selectedCell: null,
        currentFieldType: 'text',
        currentFieldLabel: '',
        currentRequired: false,
        currentDropdownOptions: '',

        // Mapped fields: [{cell_ref, field_type, field_label, ...}]
        mappedFields: [...INITIAL_FIELDS],

        // Positions
        selectedPositions: [...ASSIGNED_POSITIONS],

        init() {
            // Attach click listeners to all table cells
            document.querySelectorAll('#sheet-area .ipcrf-preview-table td').forEach(td => {
                td.addEventListener('click', (e) => {
                    const ref = td.getAttribute('data-cell');
                    if (ref) this.selectCell(ref, td);
                });
            });
            // Highlight initially mapped cells
            this.refreshCellHighlights();

            // Initialize spreadsheet column/row drag resizers
            setTimeout(() => {
                if (typeof initSpreadsheetResizers === "function") {
                    initSpreadsheetResizers();
                }
            }, 50);
        },

        selectCell(ref, el) {
            // Remove previous selection
            document.querySelectorAll('.selected-cell').forEach(c => c.classList.remove('selected-cell'));
            el.classList.add('selected-cell');

            this.selectedCell = ref;
            this.tab = 'mapper';

            // Pre-fill if already mapped
            const existing = this.mappedFields.find(f => f.cell_ref === ref);
            if (existing) {
                this.currentFieldType      = existing.field_type;
                this.currentFieldLabel     = existing.field_label || '';
                this.currentRequired       = existing.is_required || false;
                this.currentDropdownOptions = existing.field_options ? existing.field_options.join('\n') : '';
            } else {
                this.currentFieldType      = 'text';
                this.currentFieldLabel     = '';
                this.currentRequired       = false;
                this.currentDropdownOptions = '';
            }
        },

        selectCellFromField(ref) {
            const td = document.querySelector('[data-cell="' + ref + '"]');
            if (td) { this.tab = 'mapper'; this.selectCell(ref, td); }
        },

        assignField() {
            if (!this.selectedCell) return;
            const opts = this.currentDropdownOptions
                ? this.currentDropdownOptions.split('\n').map(s => s.trim()).filter(Boolean)
                : null;

            const idx = this.mappedFields.findIndex(f => f.cell_ref === this.selectedCell);
            const field = {
                cell_ref:      this.selectedCell,
                field_type:    this.currentFieldType,
                field_label:   this.currentFieldLabel,
                is_required:   this.currentRequired,
                field_options: opts,
            };
            if (idx >= 0) this.mappedFields[idx] = field;
            else          this.mappedFields.push(field);

            this.refreshCellHighlights();
            this.showToast('Field assigned to ' + this.selectedCell, 'success');
        },

        removeField(ref) {
            this.mappedFields = this.mappedFields.filter(f => f.cell_ref !== ref);
            const td = document.querySelector('[data-cell="' + ref + '"]');
            if (td) {
                td.classList.remove('mapped-cell');
                // Remove badge
                const badge = td.querySelector('.field-badge');
                if (badge) badge.remove();
            }
            if (this.selectedCell === ref) this.selectedCell = null;
        },

        isCellMapped(ref) {
            return !!this.mappedFields.find(f => f.cell_ref === ref);
        },

        refreshCellHighlights() {
            // Reset all
            document.querySelectorAll('.ipcrf-preview-table td').forEach(td => {
                td.classList.remove('mapped-cell');
                const b = td.querySelector('.field-badge');
                if (b) b.remove();
            });
            // Re-apply
            this.mappedFields.forEach(f => {
                const td = document.querySelector('[data-cell="' + f.cell_ref + '"]');
                if (!td) return;
                td.classList.add('mapped-cell');
                const badge = document.createElement('span');
                badge.className = 'field-badge ' + f.field_type;
                badge.innerHTML = '<i class="fas ' + this.fieldTypeIcon(f.field_type) + '" style="font-size:8px;"></i> '
                    + (f.field_label || f.field_type).substring(0, 18);
                
                // Preserve drawings/images in the cell
                const drawings = td.querySelectorAll('img');
                td.innerHTML = '';
                if (drawings.length > 0) {
                    drawings.forEach(img => td.appendChild(img));
                }
                td.appendChild(badge);
            });
        },

        fieldTypeIcon(type) {
            const icons = {
                autofill_name:'fa-user', autofill_position:'fa-briefcase',
                autofill_department:'fa-building', autofill_date:'fa-calendar',
                text:'fa-font', number:'fa-hashtag', textarea:'fa-align-left',
                rating:'fa-star', dropdown:'fa-chevron-down',
                signature:'fa-signature', readonly:'fa-lock', picture: 'fa-image'
            };
            return icons[type] || 'fa-square';
        },

        fieldTypeLabel(type) {
            const labels = {
                autofill_name:'Auto-Fill Name', autofill_position:'Auto-Fill Position',
                autofill_department:'Auto-Fill Department', autofill_date:'Auto-Fill Date',
                text:'Text Input', number:'Number Input', textarea:'Text Area',
                rating:'Rating', dropdown:'Dropdown', signature:'Signature', readonly:'Read-Only',
                picture: 'Add Picture'
            };
            return labels[type] || type;
        },

        async saveAll() {
            this.saving = true;
            try {
                const r = await fetch('/admin/templates/' + TEMPLATE_ID + '/fields', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ fields: this.mappedFields }),
                });
                const d = await r.json();
                if (d.success) this.showToast('Mappings saved!', 'success');
                else           this.showToast(d.message || 'Error saving', 'error');
            } catch(e) { this.showToast('Error saving', 'error'); }
            this.saving = false;
        },

        async savePositions() {
            try {
                const r = await fetch('/admin/templates/' + TEMPLATE_ID + '/positions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ position_ids: this.selectedPositions }),
                });
                const d = await r.json();
                if (d.success) this.showToast('Position access updated!', 'success');
                else           this.showToast(d.message || 'Error', 'error');
            } catch(e) { this.showToast('Error saving positions', 'error'); }
        },

        triggerAdminPictureUpload() {
            if (!this.selectedCell) {
                this.showToast('Please select a cell first!', 'error');
                return;
            }
            document.getElementById('admin-picture-uploader').click();
        },

        async handleAdminPictureSelected(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.saving = true;
            this.showToast('Uploading picture to cell ' + this.selectedCell + '...', 'success');

            const formData = new FormData();
            formData.append('file', file);
            formData.append('cell_ref', this.selectedCell);
            formData.append('_token', CSRF);

            try {
                const res = await fetch('/admin/templates/' + TEMPLATE_ID + '/upload-picture', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('Picture uploaded to cell ' + this.selectedCell + '!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showToast(data.message || 'Upload failed', 'error');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Connection error', 'error');
            }
            this.saving = false;
        },

        showToast(msg, type = 'success') {
            this.toast = { message: msg, type };
            const el = document.getElementById('toast');
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 3000);
        },
    };
}

function initSpreadsheetResizers() {
    // Column Resizers
    document.querySelectorAll('.col-resizer').forEach((resizer) => {
        resizer.addEventListener('mousedown', (e) => {
            e.preventDefault();
            e.stopPropagation();

            resizer.classList.add('dragging');
            const startX = e.clientX;
            const thEl = resizer.closest('.ipcrf-hdr-col');
            if (!thEl) return;

            const colLetter = thEl.textContent.trim();
            const colIdx = parseInt(thEl.getAttribute('data-col-idx'));

            // Retrieve the corresponding <col> in colgroup
            // In our table parser we added 1 col at the start, so colIndex in colgroup is colIdx + 1
            const colEl = document.querySelector(`.ipcrf-preview-table colgroup col:nth-child(${colIdx + 1})`);
            if (!colEl) return;

            const startWidth = colEl.getBoundingClientRect().width || 80;

            const onMouseMove = (moveEvent) => {
                const dx = moveEvent.clientX - startX;
                const newWidth = Math.max(30, startWidth + dx);
                colEl.style.width = newWidth + 'px';
            };

            const onMouseUp = () => {
                resizer.classList.remove('dragging');
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });

    // Row Resizers
    document.querySelectorAll('.row-resizer').forEach((resizer) => {
        resizer.addEventListener('mousedown', (e) => {
            e.preventDefault();
            e.stopPropagation();

            resizer.classList.add('dragging');
            const startY = e.clientY;
            const trEl = resizer.closest('tr');
            if (!trEl) return;

            const startHeight = trEl.getBoundingClientRect().height;

            const onMouseMove = (moveEvent) => {
                const dy = moveEvent.clientY - startY;
                const newHeight = Math.max(18, startHeight + dy);
                trEl.style.height = newHeight + 'px';
            };

            const onMouseUp = () => {
                resizer.classList.remove('dragging');
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });
}
</script>
@endpush
