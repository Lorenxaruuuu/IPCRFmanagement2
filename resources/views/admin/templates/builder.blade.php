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

/* ── Layout ──────────────────────────────────────────────────────────── */
.builder-header{background:#1e293b;border-bottom:1px solid rgba(255,255,255,.08);padding:0 20px;height:56px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.builder-body{display:flex;flex:1;overflow:hidden;}
.sheet-area{flex:1;overflow:auto;background:#f8fafc;position:relative;user-select:none;}
.side-panel{width:300px;flex-shrink:0;background:#1e293b;border-left:1px solid rgba(255,255,255,.08);display:flex;flex-direction:column;overflow:hidden;}
.sp-header{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;}
.sp-body{flex:1;overflow-y:auto;padding:14px 16px;}
.sp-footer{padding:10px 16px;border-top:1px solid rgba(255,255,255,.06);flex-shrink:0;}

/* ── Table ───────────────────────────────────────────────────────────── */
.ipcrf-preview-table{border-collapse:collapse;min-width:100%;font-size:11px;color:#1e293b;}
/* Default cell border – overridden per-cell by data-border-* attributes via JS */
.ipcrf-preview-table td.ipcrf-cell{
    border-top:1px solid #94a3b8;
    border-right:1px solid #94a3b8;
    border-bottom:1px solid #94a3b8;
    border-left:1px solid #94a3b8;
    padding:2px 4px;vertical-align:middle;cursor:pointer;position:relative;overflow:hidden;white-space:nowrap;
}
.ipcrf-preview-table td.ipcrf-cell:hover{background:rgba(99,102,241,.07)!important;outline:2px solid rgba(99,102,241,.3);z-index:1;}
.ipcrf-preview-table td.selected-cell{outline:2px solid #f59e0b!important;background:rgba(245,158,11,.06)!important;z-index:2;}
.ipcrf-preview-table td.multi-selected-cell{background:rgba(59,130,246,.12)!important;outline:2px solid rgba(59,130,246,.6)!important;z-index:3;}
.ipcrf-preview-table td.mapped-cell{outline:2px solid rgba(99,102,241,.5);background:rgba(99,102,241,.04);}
.ipcrf-preview-table td.editing-cell{background:#fffbeb!important;z-index:10;}
.ipcrf-preview-table td.col-highlighted{background:rgba(99,102,241,.06)!important;}
.ipcrf-preview-table td.row-highlighted{background:rgba(16,185,129,.06)!important;}
/* Border-none helpers */
.ipcrf-preview-table td.ipcrf-cell.no-border-top   {border-top:1px solid transparent!important;}
.ipcrf-preview-table td.ipcrf-cell.no-border-right  {border-right:1px solid transparent!important;}
.ipcrf-preview-table td.ipcrf-cell.no-border-bottom {border-bottom:1px solid transparent!important;}
.ipcrf-preview-table td.ipcrf-cell.no-border-left   {border-left:1px solid transparent!important;}

/* field badges */
.field-badge{display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;padding:1px 5px;border-radius:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.field-badge.autofill_name,.field-badge.autofill_position,.field-badge.autofill_department,
.field-badge.autofill_date,.field-badge.autofill_division_chief,.field-badge.autofill_approving_authority,
.field-badge.autofill_division_chief_position,.field-badge.autofill_approving_authority_position,
.field-badge.autofill_division_chief_signature,.field-badge.autofill_approving_authority_signature{background:#d1fae5;color:#065f46;}
.field-badge.date{background:#cffafe;color:#164e63;}
.field-badge.text,.field-badge.number,.field-badge.textarea{background:#dbeafe;color:#1e40af;}
.field-badge.rating{background:#fef3c7;color:#92400e;}
.field-badge.dropdown{background:#fde68a;color:#78350f;}
.field-badge.signature{background:#fce7f3;color:#9d174d;}
.field-badge.readonly{background:#f1f5f9;color:#475569;}
.field-badge.picture{background:#e0f2fe;color:#0369a1;}
.field-badge.calculated_mean{background:#f3e8ff;color:#6b21a8;}

/* ── Side-panel controls ─────────────────────────────────────────────── */
.sp-label{font-size:10px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.06em;}
.sp-input{width:100%;background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 10px;font-size:12px;color:#f1f5f9;outline:none;transition:border .15s;}
.sp-input:focus{border-color:#6366f1;}
.ft-grid{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-bottom:14px;}
.ft-section-title{font-size:9px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;grid-column:1/-1;margin:6px 0 2px;padding-left:2px;}
.ft-option{display:flex;align-items:center;gap:5px;padding:6px 8px;border-radius:7px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.02);cursor:pointer;font-size:10.5px;font-weight:500;color:#94a3b8;transition:all .15s;user-select:none;}
.ft-option:hover{border-color:rgba(99,102,241,.4);color:#a5b4fc;background:rgba(99,102,241,.08);}
.ft-option.active{border-color:#6366f1;background:rgba(99,102,241,.18);color:#c7d2fe;}
.ft-option i{width:13px;text-align:center;flex-shrink:0;}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;}
.btn-primary:hover{opacity:.9;}
.btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.1);}
.btn-ghost:hover{background:rgba(255,255,255,.12);color:#f1f5f9;}
.btn-danger{background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.25);}
.btn-danger:hover{background:rgba(239,68,68,.2);}
.btn-sm{padding:4px 10px;font-size:11px;}
.btn-merge{background:rgba(59,130,246,.14);color:#60a5fa;border:1px solid rgba(59,130,246,.3);}
.btn-merge:hover{background:rgba(59,130,246,.25);}
.btn-undo{background:rgba(255,255,255,.04);color:#94a3b8;border:1px solid rgba(255,255,255,.1);}
.btn-undo:hover:not(:disabled){background:rgba(99,102,241,.12);color:#a5b4fc;border-color:rgba(99,102,241,.4);}
.btn-undo:disabled{opacity:.3;cursor:not-allowed;}

/* ── Tabs ────────────────────────────────────────────────────────────── */
.tab-row{display:flex;border-bottom:1px solid rgba(255,255,255,.06);background:#1e293b;flex-shrink:0;}
.tab-btn{padding:10px 14px;font-size:12px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;border-bottom:2px solid transparent;transition:all .2s;}
.tab-btn.active{color:#a5b4fc;border-bottom-color:#6366f1;}

/* ── Field list in FIELDS tab ───────────────────────────────────────── */
.field-item{display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:rgba(15,23,42,.5);margin-bottom:6px;}
.field-item-ref{font-size:11px;font-weight:700;color:#818cf8;min-width:30px;}
.field-item-type{font-size:10px;color:#64748b;}
.field-item-label{font-size:12px;color:#f1f5f9;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

/* ── Headers ─────────────────────────────────────────────────────────── */
.ipcrf-hdr-corner,.ipcrf-hdr-col,.ipcrf-hdr-row{
    background:#f1f5f9!important;color:#64748b!important;font-weight:700!important;
    font-size:10px!important;text-align:center!important;border:1px solid #cbd5e1!important;
    user-select:none!important;position:relative!important;
}
.ipcrf-hdr-corner{width:40px;height:24px;}
.ipcrf-hdr-col{height:24px;vertical-align:middle!important;cursor:pointer!important;}
.ipcrf-hdr-row{width:40px;vertical-align:middle!important;cursor:pointer!important;}
.ipcrf-hdr-col:hover,.ipcrf-hdr-row:hover{background:#dde3ed!important;}
.ipcrf-hdr-col.hdr-selected{background:#c7d2fe!important;color:#3730a3!important;}
.ipcrf-hdr-row.hdr-selected{background:#a7f3d0!important;color:#065f46!important;}

/* ── Resizers ────────────────────────────────────────────────────────── */
.col-resizer{position:absolute;right:0;top:0;bottom:0;width:5px;cursor:col-resize;z-index:10;background:transparent;transition:background .15s;}
.col-resizer:hover,.col-resizer.dragging{background:#6366f1;}
.row-resizer{position:absolute;left:0;right:0;bottom:0;height:5px;cursor:row-resize;z-index:10;background:transparent;transition:background .15s;}
.row-resizer:hover,.row-resizer.dragging{background:#6366f1;}

/* ── Inline editor ───────────────────────────────────────────────────── */
.cell-inline-editor{
    position:absolute;top:0;left:0;width:100%;height:100%;
    border:2px solid #6366f1!important;background:#fffbeb!important;
    color:#1e293b!important;font-family:inherit!important;font-size:inherit!important;
    font-weight:inherit!important;text-align:inherit!important;
    padding:2px 6px!important;z-index:200;box-sizing:border-box!important;outline:none!important;
}
.ipcrf-preview-table input,.ipcrf-preview-table textarea,.ipcrf-preview-table select{
    width:100%!important;height:100%!important;border:none!important;background:transparent!important;
    outline:none!important;font-family:inherit!important;font-size:inherit!important;
    color:inherit!important;padding:2px 4px!important;box-sizing:border-box!important;margin:0!important;
}

/* ── Structure toolbar ───────────────────────────────────────────────── */
.struct-bar{background:#0f172a;border-bottom:1px solid rgba(255,255,255,.05);padding:5px 14px;display:flex;align-items:center;gap:5px;flex-shrink:0;flex-wrap:wrap;}
.struct-lbl{font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;}
.struct-sep{width:1px;height:18px;background:rgba(255,255,255,.07);margin:0 3px;flex-shrink:0;}
.struct-btn{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font-size:10.5px;font-weight:600;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.03);color:#94a3b8;transition:all .15s;white-space:nowrap;}
.struct-btn:hover{background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.35);color:#a5b4fc;}
.struct-btn.del:hover{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#ef4444;}
.struct-btn.merge-action{border-color:rgba(59,130,246,.3);color:#60a5fa;}
.struct-btn.merge-action:hover{background:rgba(59,130,246,.15);border-color:#3b82f6;}
.struct-btn.border-btn{border-color:rgba(251,191,36,.25);color:#fbbf24;}
.struct-btn.border-btn:hover{background:rgba(251,191,36,.12);border-color:#fbbf24;}
.struct-btn.border-none-btn{border-color:rgba(100,116,139,.3);color:#64748b;}
.struct-btn.border-none-btn:hover{background:rgba(239,68,68,.1);border-color:#ef4444;color:#ef4444;}
/* Format buttons (Bold / Italic / Underline) */
.struct-btn.fmt-btn{border-color:rgba(167,139,250,.3);color:#a78bfa;}
.struct-btn.fmt-btn:hover{background:rgba(167,139,250,.14);border-color:#a78bfa;color:#c4b5fd;}
.struct-btn.fmt-btn.active-fmt{background:rgba(167,139,250,.22)!important;border-color:#a78bfa!important;color:#c4b5fd!important;}
/* Ctrl+RightClick merge-select highlight */
.ipcrf-preview-table td.merge-select-cell{background:rgba(139,92,246,.18)!important;outline:2px dashed rgba(139,92,246,.8)!important;z-index:4;}
.struct-hint{font-size:10px;color:#334155;margin-left:auto;}

/* ── Resize popup ────────────────────────────────────────────────────── */
.resize-panel{display:flex;align-items:center;gap:6px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:8px;padding:4px 10px;margin-left:auto;}
.resize-panel input{width:58px;background:#0f172a;border:1px solid rgba(255,255,255,.15);border-radius:6px;padding:3px 6px;font-size:11px;color:#f1f5f9;outline:none;text-align:center;}
.resize-panel input:focus{border-color:#6366f1;}

/* ── Context menu ────────────────────────────────────────────────────── */
.ctx-menu{position:fixed;background:#1e293b;border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:6px;z-index:10000;min-width:190px;box-shadow:0 10px 40px rgba(0,0,0,.5);}
.ctx-item{display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:7px;font-size:12px;font-weight:500;color:#cbd5e1;cursor:pointer;transition:all .15s;}
.ctx-item:hover{background:rgba(99,102,241,.15);color:#a5b4fc;}
.ctx-item.del:hover{background:rgba(239,68,68,.12);color:#ef4444;}
.ctx-item.merge:hover{background:rgba(59,130,246,.12);color:#60a5fa;}
.ctx-sep{border:none;border-top:1px solid rgba(255,255,255,.06);margin:4px 0;}

/* ── Toast ───────────────────────────────────────────────────────────── */
.toast{position:fixed;bottom:20px;right:20px;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 16px;font-size:13px;color:#f1f5f9;z-index:9999;transform:translateY(20px);opacity:0;transition:all .3s;min-width:260px;display:flex;align-items:center;gap:8px;pointer-events:none;}
.toast.show{transform:translateY(0);opacity:1;}
.toast-success{border-left:3px solid #10b981;}
.toast-error  {border-left:3px solid #ef4444;}
.toast-info   {border-left:3px solid #6366f1;}
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
                <p style="font-size:11px;color:#475569;margin:0;">
                    Click → assign field &nbsp;·&nbsp; Double-click → edit text &nbsp;·&nbsp;
                    Drag / Shift+click / Ctrl+click → multi-select &nbsp;·&nbsp; Click header → resize &nbsp;·&nbsp; Right-click header → add/delete
                </p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:11px;color:#64748b;" x-text="mappedFields.length + ' field(s) mapped'"></span>
            {{-- Undo / Redo --}}
            <button class="btn btn-undo btn-sm" @click="undo()" :disabled="_historyIdx <= 0" title="Undo (Ctrl+Z)">
                <i class="fas fa-undo"></i>
            </button>
            <button class="btn btn-undo btn-sm" @click="redo()" :disabled="_historyIdx >= _history.length - 1" title="Redo (Ctrl+Shift+Z)">
                <i class="fas fa-redo"></i>
            </button>
            <button class="btn btn-ghost btn-sm" @click="tab='positions'"><i class="fas fa-id-badge"></i> Positions</button>
            <button class="btn btn-success" @click="saveAll()">
                <span x-show="!saving"><i class="fas fa-save"></i> Save All</span>
                <span x-show="saving"><i class="fas fa-spinner fa-spin"></i> Saving…</span>
            </button>
        </div>
    </div>

    {{-- ── TABS ── --}}
    <div class="tab-row">
        <button class="tab-btn" :class="{active:tab==='mapper'}"    @click="tab='mapper'"><i class="fas fa-magic"    style="margin-right:5px;"></i>Field Mapper</button>
        <button class="tab-btn" :class="{active:tab==='fields'}"    @click="tab='fields'"><i class="fas fa-list"     style="margin-right:5px;"></i>Fields (<span x-text="mappedFields.length"></span>)</button>
        <button class="tab-btn" :class="{active:tab==='positions'}" @click="tab='positions'"><i class="fas fa-id-badge" style="margin-right:5px;"></i>Positions</button>
    </div>

    {{-- ── STRUCTURE + MERGE TOOLBAR ── --}}
    <div class="struct-bar">
        {{-- Row/col operations --}}
        <span class="struct-lbl"><i class="fas fa-table" style="margin-right:3px;"></i>Rows/Cols</span>
        <button class="struct-btn" @click="promptAddRow('above')"><i class="fas fa-plus"></i> Row Above</button>
        <button class="struct-btn" @click="promptAddRow('below')"><i class="fas fa-plus"></i> Row Below</button>
        <button class="struct-btn del" @click="promptDeleteRow()"><i class="fas fa-minus"></i> Del Row</button>
        <div class="struct-sep"></div>
        <button class="struct-btn" @click="promptAddCol('left')"><i class="fas fa-plus"></i> Col Left</button>
        <button class="struct-btn" @click="promptAddCol('right')"><i class="fas fa-plus"></i> Col Right</button>
        <button class="struct-btn del" @click="promptDeleteCol()"><i class="fas fa-minus"></i> Del Col</button>

        {{-- Merge controls (show when 2+ cells selected or a merged cell is active) --}}
        <template x-if="selectedCells.length >= 2 || selectedMergedCell">
            <div style="display:flex;align-items:center;gap:5px;">
                <div class="struct-sep"></div>
                <span class="struct-lbl" style="color:#60a5fa;"><i class="fas fa-object-group" style="margin-right:3px;"></i>Merge</span>
                <button class="struct-btn merge-action" x-show="selectedCells.length >= 2" @click="mergeCells()">
                    <i class="fas fa-compress-alt"></i>
                    Merge (<span x-text="selectedCells.length"></span> cells)
                </button>
                <button class="struct-btn del" x-show="selectedMergedCell" @click="unmergeCells()">
                    <i class="fas fa-expand-alt"></i> Unmerge
                </button>
            </div>
        </template>

        {{-- Border controls (show when 1+ cell selected) --}}
        <template x-if="selectedCell || selectedCells.length >= 1">
            <div style="display:flex;align-items:center;gap:4px;">
                <div class="struct-sep"></div>
                <span class="struct-lbl" style="color:#fbbf24;"><i class="fas fa-border-all" style="margin-right:3px;"></i>Borders</span>
                <button class="struct-btn border-btn" title="All Borders" @click="applyBorder('all')">
                    <i class="fas fa-border-all"></i> All
                </button>
                <button class="struct-btn border-btn" title="Border Top" @click="applyBorder('top')">
                    <i class="fas fa-border-top"></i> Top
                </button>
                <button class="struct-btn border-btn" title="Border Bottom" @click="applyBorder('bottom')">
                    <i class="fas fa-border-bottom"></i> Bottom
                </button>
                <button class="struct-btn border-btn" title="Border Left" @click="applyBorder('left')">
                    <i class="fas fa-border-left"></i> Left
                </button>
                <button class="struct-btn border-btn" title="Border Right" @click="applyBorder('right')">
                    <i class="fas fa-border-right"></i> Right
                </button>
                <button class="struct-btn border-none-btn" title="Remove All Borders" @click="applyBorder('none')">
                    <i class="fas fa-border-none"></i> None
                </button>
            </div>
        </template>

        {{-- Text Formatting: Bold / Italic / Underline (show when 1+ cell selected) --}}
        <template x-if="selectedCell || selectedCells.length >= 1">
            <div style="display:flex;align-items:center;gap:4px;">
                <div class="struct-sep"></div>
                <span class="struct-lbl" style="color:#a78bfa;"><i class="fas fa-font" style="margin-right:3px;"></i>Format</span>
                <button class="struct-btn fmt-btn" :class="{'active-fmt': _activeFmt.bold}"
                        title="Bold (Ctrl+B)" @click="applyFormat('bold')">
                    <b>B</b>
                </button>
                <button class="struct-btn fmt-btn" :class="{'active-fmt': _activeFmt.italic}"
                        title="Italic (Ctrl+I)" @click="applyFormat('italic')">
                    <i>I</i>
                </button>
                <button class="struct-btn fmt-btn" :class="{'active-fmt': _activeFmt.underline}"
                        title="Underline (Ctrl+U)" @click="applyFormat('underline')">
                    <span style="text-decoration:underline;">U</span>
                </button>
            </div>
        </template>

        {{-- Alignment controls (show when 1+ cell selected) --}}
        <template x-if="selectedCell || selectedCells.length >= 1">
            <div style="display:flex;align-items:center;gap:4px;">
                <div class="struct-sep"></div>
                <span class="struct-lbl" style="color:#60a5fa;"><i class="fas fa-align-left" style="margin-right:3px;"></i>Align</span>
                <button class="struct-btn fmt-btn" title="Align Left" @click="applyAlignment('left')">
                    <i class="fas fa-align-left"></i>
                </button>
                <button class="struct-btn fmt-btn" title="Align Center" @click="applyAlignment('center')">
                    <i class="fas fa-align-center"></i>
                </button>
                <button class="struct-btn fmt-btn" title="Align Right" @click="applyAlignment('right')">
                    <i class="fas fa-align-right"></i>
                </button>
            </div>
        </template>

        {{-- Resize panel (shown when a header is clicked) --}}
        <template x-if="activeHdrType">
            <div class="resize-panel">
                <i class="fas fa-arrows-alt-h" x-show="activeHdrType==='col'" style="color:#818cf8;font-size:11px;"></i>
                <i class="fas fa-arrows-alt-v" x-show="activeHdrType==='row'" style="color:#34d399;font-size:11px;"></i>
                <span class="struct-lbl" style="margin:0;" x-text="activeHdrType==='col' ? 'Column Width' : 'Row Height'"></span>
                <input type="number" x-model.number="activeHdrSize" min="10" max="800"
                       @keydown.enter="applyHdrResize()" placeholder="px">
                <span style="font-size:10px;color:#475569;">px</span>
                <button class="struct-btn" style="padding:3px 8px;" @click="applyHdrResize()"><i class="fas fa-check"></i></button>
                <button class="struct-btn del" style="padding:3px 7px;" @click="clearHdrSelection()">✕</button>
            </div>
        </template>

        <span class="struct-hint" x-show="!activeHdrType && !selectedCell && selectedCells.length < 1">
            <i class="fas fa-info-circle" style="margin-right:3px;"></i>
            Drag / Shift+click / Ctrl+click → select · Ctrl+Right-click → free-pick for merge · B/I/U format
        </span>
    </div>

    {{-- ── BODY ── --}}
    <div class="builder-body" @click="onBodyClick()">

        {{-- Spreadsheet --}}
        <div class="sheet-area" id="sheet-area">
            {!! $htmlTable !!}
        </div>

        {{-- Side Panel --}}
        <div class="side-panel" @click.stop>

            {{-- MAPPER TAB --}}
            <template x-if="tab==='mapper'">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="sp-header">
                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0 0 3px;">
                            <template x-if="selectedCell">
                                Cell: <span style="color:#f59e0b;" x-text="selectedCell"></span>
                                <span x-show="selectedMergedCell" style="font-size:10px;background:rgba(59,130,246,.2);color:#60a5fa;border-radius:4px;padding:1px 5px;margin-left:4px;">MERGED</span>
                            </template>
                            <template x-if="!selectedCell && selectedCells.length === 0">
                                <span style="color:#475569;">No cell selected</span>
                            </template>
                            <template x-if="!selectedCell && selectedCells.length > 0">
                                <span style="color:#60a5fa;"><span x-text="selectedCells.length"></span> cells selected</span>
                            </template>
                        </p>
                        <p style="font-size:11px;color:#475569;margin:0;">Click cell · Double-click to edit text · Drag to multi-select</p>
                    </div>

                    <div class="sp-body" x-show="!selectedCell" style="display:flex;align-items:center;justify-content:center;height:100%;">
                        <div style="text-align:center;color:#334155;padding:20px;">
                            <i class="fas fa-mouse-pointer" style="font-size:36px;display:block;margin-bottom:14px;color:#475569;"></i>
                            <p style="font-size:13px;margin:0 0 8px;">Click any cell to configure a field type</p>
                            <p style="font-size:11px;color:#475569;margin:0;">
                                Double-click → edit cell text<br>
                                Drag / Shift+click → select range then merge<br>
                                Click col/row header → resize
                            </p>
                        </div>
                    </div>

                    <div class="sp-body" x-show="selectedCell">
                        <label class="sp-label">Field Type</label>
                        <div class="ft-grid">
                            <span class="ft-section-title">Auto-Fill</span>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_name'}"                 @click="currentFieldType='autofill_name'"><i class="fas fa-user"></i>Employee Name</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_position'}"             @click="currentFieldType='autofill_position'"><i class="fas fa-briefcase"></i>Position</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_department'}"           @click="currentFieldType='autofill_department'"><i class="fas fa-building"></i>Department</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_date'}"                 @click="currentFieldType='autofill_date'"><i class="fas fa-calendar-day"></i>Date Signed</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_division_chief'}"       @click="currentFieldType='autofill_division_chief'"><i class="fas fa-user-tie"></i>Division Chief</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_division_chief_position'}" @click="currentFieldType='autofill_division_chief_position'"><i class="fas fa-briefcase"></i>Div. Chief Position</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_division_chief_signature'}" @click="currentFieldType='autofill_division_chief_signature'"><i class="fas fa-signature"></i>Div. Chief Signature</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_approving_authority'}"  @click="currentFieldType='autofill_approving_authority'"><i class="fas fa-stamp"></i>Approving Auth.</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_approving_authority_position'}" @click="currentFieldType='autofill_approving_authority_position'"><i class="fas fa-briefcase"></i>Appr. Auth Position</div>
                            <div class="ft-option" :class="{active:currentFieldType==='autofill_approving_authority_signature'}" @click="currentFieldType='autofill_approving_authority_signature'"><i class="fas fa-signature"></i>Appr. Auth Signature</div>

                            <span class="ft-section-title">Input Fields</span>
                            <div class="ft-option" :class="{active:currentFieldType==='date'}"      @click="currentFieldType='date'"><i class="fas fa-calendar-alt"></i>Date Picker</div>
                            <div class="ft-option" :class="{active:currentFieldType==='text'}"      @click="currentFieldType='text'"><i class="fas fa-font"></i>Text Input</div>
                            <div class="ft-option" :class="{active:currentFieldType==='number'}"    @click="currentFieldType='number'"><i class="fas fa-hashtag"></i>Number</div>
                            <div class="ft-option" :class="{active:currentFieldType==='textarea'}"  @click="currentFieldType='textarea'"><i class="fas fa-align-left"></i>Text Area</div>
                            <div class="ft-option" :class="{active:currentFieldType==='rating'}"    @click="currentFieldType='rating'"><i class="fas fa-star"></i>Rating</div>
                            <div class="ft-option" :class="{active:currentFieldType==='dropdown'}"  @click="currentFieldType='dropdown'"><i class="fas fa-chevron-down"></i>Dropdown</div>
                            <div class="ft-option" :class="{active:currentFieldType==='signature'}" @click="currentFieldType='signature'"><i class="fas fa-signature"></i>Signature</div>
                            <div class="ft-option" :class="{active:currentFieldType==='readonly'}"  @click="currentFieldType='readonly'"><i class="fas fa-lock"></i>Read-Only</div>
                            <div class="ft-option" :class="{active:currentFieldType==='calculated_mean'}" @click="currentFieldType='calculated_mean'"><i class="fas fa-calculator"></i>Calculated Mean</div>

                            <span class="ft-section-title">Media</span>
                            <div class="ft-option" style="grid-column:1/-1;"
                                 :class="{active:currentFieldType==='picture'}"
                                 @click="currentFieldType='picture'; $nextTick(()=>{ if(selectedCell) triggerPictureUpload(); })">
                                 <i class="fas fa-folder-open"></i>Add Picture (opens file manager)
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label class="sp-label">Field Label <span style="font-weight:400;text-transform:none;">(optional)</span></label>
                            <input type="text" class="sp-input" placeholder="e.g., Employee Full Name" x-model="currentFieldLabel">
                        </div>
                        <div x-show="currentFieldType==='dropdown'" style="margin-bottom:10px;">
                            <label class="sp-label">Options <span style="font-weight:400;text-transform:none;">(one per line)</span></label>
                            <textarea class="sp-input" rows="3" x-model="currentDropdownOptions" style="resize:vertical;"></textarea>
                        </div>
                        <div x-show="currentFieldType==='calculated_mean'" style="margin-bottom:10px;">
                            <label class="sp-label">Target Cells <span style="font-weight:400;text-transform:none;">(comma-separated, e.g. D10,E10,F10)</span></label>
                            <input type="text" class="sp-input" placeholder="e.g., D10,E10,F10" x-model="currentMeanTargets">
                        </div>
                        <div style="margin-bottom:14px;display:flex;align-items:center;gap:8px;">
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
                </div>
            </template>

            {{-- FIELDS LIST TAB --}}
            <template x-if="tab==='fields'">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="sp-header">
                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0;">Mapped Fields</p>
                        <p style="font-size:11px;color:#64748b;margin:0;" x-text="mappedFields.length + ' cell(s) configured'"></p>
                    </div>
                    <div class="sp-body">
                        <template x-if="mappedFields.length===0">
                            <div style="text-align:center;color:#334155;padding:40px 0;">
                                <i class="fas fa-th" style="font-size:28px;display:block;margin-bottom:12px;color:#475569;"></i>
                                <p style="font-size:12px;">No fields mapped yet.</p>
                            </div>
                        </template>
                        <template x-for="f in mappedFields" :key="f.cell_ref">
                            <div class="field-item" @click="selectCellFromField(f.cell_ref)" style="cursor:pointer;">
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
                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0;">Position Access</p>
                        <p style="font-size:11px;color:#64748b;margin:0;">Which positions can see this template</p>
                    </div>
                    <div class="sp-body">
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 12px;">Select all positions that should have access:</p>
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
                                <p style="font-size:12px;">No positions yet. <a href="{{ route('admin.dashboard') }}" style="color:#6366f1;">Add positions first.</a></p>
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

    {{-- ── CONTEXT MENU ── --}}
    <div class="ctx-menu" x-show="ctxMenu.show" x-cloak @click.stop
         :style="`left:${ctxMenu.x}px;top:${ctxMenu.y}px;`" style="display:none;">
        <template x-if="ctxMenu.type==='col'">
            <div>
                <div class="ctx-item" @click="addColAt(ctxMenu.index,'left')"><i class="fas fa-plus" style="color:#10b981;width:14px;"></i> Add Column Left</div>
                <div class="ctx-item" @click="addColAt(ctxMenu.index,'right')"><i class="fas fa-plus" style="color:#10b981;width:14px;"></i> Add Column Right</div>
                <hr class="ctx-sep">
                <div class="ctx-item del" @click="deleteColAt(ctxMenu.index)"><i class="fas fa-trash" style="width:14px;"></i> Delete This Column</div>
            </div>
        </template>
        <template x-if="ctxMenu.type==='row'">
            <div>
                <div class="ctx-item" @click="addRowAt(ctxMenu.index,'above')"><i class="fas fa-plus" style="color:#10b981;width:14px;"></i> Add Row Above</div>
                <div class="ctx-item" @click="addRowAt(ctxMenu.index,'below')"><i class="fas fa-plus" style="color:#10b981;width:14px;"></i> Add Row Below</div>
                <hr class="ctx-sep">
                <div class="ctx-item del" @click="deleteRowAt(ctxMenu.index)"><i class="fas fa-trash" style="width:14px;"></i> Delete This Row</div>
            </div>
        </template>
        <template x-if="ctxMenu.type==='cell'">
            <div>
                <div class="ctx-item" @click="addCell('left')"><i class="fas fa-arrow-left" style="color:#10b981;width:14px;"></i> Add Cell Left</div>
                <div class="ctx-item" @click="addCell('right')"><i class="fas fa-arrow-right" style="color:#10b981;width:14px;"></i> Add Cell Right</div>
            </div>
        </template>
    </div>

    {{-- ── TOAST ── --}}
    <div class="toast" :class="toast.type ? 'toast-'+toast.type : ''" id="toast">
        <i :class="{
             'fas fa-check-circle':       toast.type==='success',
             'fas fa-info-circle':        toast.type==='info',
             'fas fa-exclamation-circle': toast.type==='error'
           }"
           :style="'color:'+(toast.type==='success'?'#10b981':toast.type==='info'?'#6366f1':'#ef4444')"></i>
        <span x-text="toast.message"></span>
    </div>

    {{-- Hidden file input for picture upload --}}
    <input type="file" id="picture-file-input" accept="image/*" style="display:none;"
           @change="handlePictureUpload($event)">

</div>{{-- /builder-layout --}}
@endsection

@push('scripts')
<script>
const CSRF        = '{{ csrf_token() }}';
const TEMPLATE_ID = {{ $template->id }};
const INITIAL_FIELDS     = @json($mappedFields);
const ASSIGNED_POSITIONS = @json($template->positions->pluck('id')->toArray());

/* Store hidden cells outside Alpine for performance (not reactive) */
const _hiddenCells = {};  // { primaryRef: [{ref, rowNum, colNum, text, html}] }

function builderApp() {
    return {
        tab: 'mapper',
        saving: false,
        toast: { message: '', type: '' },

        /* single cell */
        selectedCell: null,
        currentFieldType: 'text',
        currentFieldLabel: '',
        currentRequired: false,
        currentDropdownOptions: '',
        currentMeanTargets: '',

        /* multi-cell selection */
        selectedCells:     [],    // [{ref, td}]
        selectionAnchor:   null,  // {ref, row, col}
        selectedMergedCell: null, // ref of a merged cell when selected alone
        _isDragging:   false,
        _dragMoved:    false,
        _dragStartTd:  null,

        /* mapped fields & positions */
        mappedFields:      [...INITIAL_FIELDS],
        selectedPositions: [...ASSIGNED_POSITIONS],

        /* context menu */
        ctxMenu: { show:false, x:0, y:0, type:null, index:null },

        /* header resize */
        activeHdrType: null,   // 'col' | 'row' | null
        activeHdrIdx:  null,
        activeHdrEl:   null,
        activeHdrSize: 80,

        /* inline edit guard */
        _editingRef: null,

        /* active format state for toolbar button highlight */
        _activeFmt: { bold: false, italic: false, underline: false },

        /* ─── Undo / Redo history ────────────────────────────────────────── */
        _history:    [],   // array of { tableHTML, mappedFields, hiddenCells }
        _historyIdx: -1,   // current position in _history

        /* ─── Init ──────────────────────────────────────────────────────── */
        init() {
            this.setupEventDelegation();
            this.refreshCellHighlights();
            setTimeout(() => {
                if (typeof initSpreadsheetResizers === 'function') initSpreadsheetResizers();
                /* Push the very first snapshot so Undo has a baseline */
                this._pushHistory();
            }, 80);
            document.addEventListener('contextmenu', e => e.preventDefault());
            document.addEventListener('mouseup',     e => this._onDocMouseUp(e));
            document.addEventListener('click',       ()=> this.hideCtxMenu());
            /* ── Keyboard shortcuts: Ctrl+Z / Ctrl+Shift+Z / Ctrl+Y / Ctrl+B/I/U ── */
            document.addEventListener('keydown', e => {
                if (this._editingRef) return;          // let inline editor handle its own keys
                const ctrl = e.ctrlKey || e.metaKey;
                if (ctrl && !e.shiftKey && e.key.toLowerCase() === 'z') {
                    e.preventDefault(); this.undo();
                } else if (ctrl && e.shiftKey && e.key.toLowerCase() === 'z') {
                    e.preventDefault(); this.redo();
                } else if (ctrl && !e.shiftKey && e.key.toLowerCase() === 'y') {
                    e.preventDefault(); this.redo();
                } else if (ctrl && e.key.toLowerCase() === 'b') {
                    e.preventDefault(); this.applyFormat('bold');
                } else if (ctrl && e.key.toLowerCase() === 'i') {
                    e.preventDefault(); this.applyFormat('italic');
                } else if (ctrl && e.key.toLowerCase() === 'u') {
                    e.preventDefault(); this.applyFormat('underline');
                }
            });
        },

        /* ─── Event Delegation on #sheet-area ──────────────────────────── */
        setupEventDelegation() {
            const sheet = document.getElementById('sheet-area');
            if (!sheet) return;

            /* Right-click: (a) Ctrl+RightClick on data cell = toggle merge-select
                             (b) Plain right-click on header  = show add/del context menu */
            sheet.addEventListener('contextmenu', e => {
                const td = e.target.closest('td');
                if (!td) return;
                e.preventDefault(); e.stopPropagation();

                /* ── Ctrl + Right-Click: free-pick cell for merging ── */
                if ((e.ctrlKey || e.metaKey) && td.classList.contains('ipcrf-cell')) {
                    const ref = td.getAttribute('data-cell');
                    if (!ref) return;
                    const existIdx = this.selectedCells.findIndex(c => c.ref === ref);
                    if (existIdx >= 0) {
                        // Already in selection — deselect
                        td.classList.remove('merge-select-cell', 'multi-selected-cell');
                        this.selectedCells.splice(existIdx, 1);
                    } else {
                        // Add to selection
                        td.classList.remove('selected-cell');
                        td.classList.add('merge-select-cell');
                        this.selectedCells.push({ ref, td });
                        // Fold any existing single-selection in
                        if (this.selectedCell && this.selectedCell !== ref) {
                            const prevTd = document.querySelector('[data-cell="' + this.selectedCell + '"]');
                            if (prevTd) {
                                prevTd.classList.remove('selected-cell');
                                prevTd.classList.add('merge-select-cell');
                                if (!this.selectedCells.find(c => c.ref === this.selectedCell))
                                    this.selectedCells.push({ ref: this.selectedCell, td: prevTd });
                            }
                            this.selectedCell = null;
                        }
                    }
                    this.selectedMergedCell = null;
                    return;
                }

                /* ── Plain right-click on column/row header ── */
                if (td.classList.contains('ipcrf-hdr-col')) {
                    this.showCtxMenu(e.clientX, e.clientY, 'col', +td.getAttribute('data-col-idx'));
                } else if (td.classList.contains('ipcrf-hdr-row')) {
                    this.showCtxMenu(e.clientX, e.clientY, 'row', +td.getAttribute('data-row-idx'));
                } else if (td.classList.contains('ipcrf-cell')) {
                    const ref = td.getAttribute('data-cell');
                    if (ref) {
                        this.selectCell(ref, td);
                        this.showCtxMenu(e.clientX, e.clientY, 'cell', ref);
                    }
                }
            });

            /* Mousedown: start drag-select OR click a header */
            sheet.addEventListener('mousedown', e => {
                if (e.button !== 0) return;
                const td = e.target.closest('td');
                if (!td) return;

                const isResizer = e.target.classList.contains('col-resizer') || e.target.classList.contains('row-resizer');
                if (isResizer) return;

                const isColHdr = td.classList.contains('ipcrf-hdr-col');
                const isRowHdr = td.classList.contains('ipcrf-hdr-row');
                const isCorner = td.classList.contains('ipcrf-hdr-corner');
                if (isCorner) return;

                /* col header click → select entire column & show resize control */
                if (isColHdr) {
                    e.stopPropagation();
                    this.clickColHeader(+td.getAttribute('data-col-idx'), td);
                    return;
                }
                /* row header click → select entire row & show resize control */
                if (isRowHdr) {
                    e.stopPropagation();
                    this.clickRowHeader(+td.getAttribute('data-row-idx'), td);
                    return;
                }

                const ref = td.getAttribute('data-cell');
                if (!ref) return;

                this.clearHdrSelection();
                this.hideCtxMenu();

                /* ── Ctrl+Click: toggle this cell in/out of multi-selection ── */
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this._isDragging  = false;
                    this._dragMoved   = false;
                    this._dragStartTd = null;
                    const existIdx = this.selectedCells.findIndex(c => c.ref === ref);
                    if (existIdx >= 0) {
                        // Deselect this cell
                        td.classList.remove('multi-selected-cell');
                        this.selectedCells.splice(existIdx, 1);
                    } else {
                        // Add this cell to selection
                        td.classList.add('multi-selected-cell');
                        td.classList.remove('selected-cell');
                        this.selectedCells.push({ ref, td });
                        // If we had a single selected cell, fold it into the multi-select
                        if (this.selectedCell && this.selectedCell !== ref) {
                            const prevTd = document.querySelector('[data-cell="' + this.selectedCell + '"]');
                            if (prevTd) {
                                prevTd.classList.remove('selected-cell');
                                prevTd.classList.add('multi-selected-cell');
                                if (!this.selectedCells.find(c => c.ref === this.selectedCell))
                                    this.selectedCells.push({ ref: this.selectedCell, td: prevTd });
                            }
                        }
                        this.selectedCell = null;
                    }
                    // Recompute anchor for possible future shift-extend
                    this.selectionAnchor = this.selectedCells.length
                        ? { ref: this.selectedCells[this.selectedCells.length - 1].ref,
                            row: +this.selectedCells[this.selectedCells.length - 1].td.getAttribute('data-row'),
                            col: +this.selectedCells[this.selectedCells.length - 1].td.getAttribute('data-col') }
                        : null;
                    this.selectedMergedCell = null;
                    return;
                }

                /* ── Normal drag-select ── */
                this._isDragging  = true;
                this._dragMoved   = false;
                this._dragStartTd = td;

                if (e.shiftKey && this.selectionAnchor) {
                    this.extendSelectionTo(ref, td);
                } else if (!e.shiftKey) {
                    this.clearMultiSelection();
                    this.selectionAnchor = {
                        ref,
                        row: +td.getAttribute('data-row'),
                        col: +td.getAttribute('data-col'),
                    };
                }
            });

            /* Mouseover: extend drag selection */
            sheet.addEventListener('mouseover', e => {
                if (!this._isDragging) return;
                const td = e.target.closest('td[data-cell]');
                if (!td || td.classList.contains('ipcrf-hdr-col') || td.classList.contains('ipcrf-hdr-row')) return;
                const ref = td.getAttribute('data-cell');
                if (!ref || td === this._dragStartTd) return;
                this._dragMoved = true;
                this.extendSelectionTo(ref, td);
            });

            /* Double-click: inline text editor */
            sheet.addEventListener('dblclick', e => {
                const td = e.target.closest('td[data-cell]');
                if (!td || td.classList.contains('ipcrf-hdr-col') || td.classList.contains('ipcrf-hdr-row') || td.classList.contains('ipcrf-hdr-corner')) return;
                const ref = td.getAttribute('data-cell');
                if (ref) this.startInlineEdit(ref, td);
            });
        },

        /* global mouseup: finalise single-click or drag */
        _onDocMouseUp(e) {
            if (e.button !== 0 || !this._isDragging) return;
            this._isDragging = false;

            if (!this._dragMoved && this._dragStartTd) {
                const td  = this._dragStartTd;
                const ref = td.getAttribute('data-cell');
                if (ref) {
                    this.clearMultiSelection();
                    this.selectCell(ref, td);
                    this.selectionAnchor = {
                        ref,
                        row: +td.getAttribute('data-row'),
                        col: +td.getAttribute('data-col'),
                    };
                    /* detect if it is a merged cell */
                    const rs = +td.getAttribute('rowspan') || 1;
                    const cs = +td.getAttribute('colspan') || 1;
                    this.selectedMergedCell = (rs > 1 || cs > 1) ? ref : null;
                }
            }
            this._dragStartTd = null;
        },

        onBodyClick() {
            this.hideCtxMenu();
        },

        /* ─── Multi-Cell Selection ───────────────────────────────────────── */
        extendSelectionTo(endRef, endTd) {
            if (!this.selectionAnchor) return;
            const { row: ar, col: ac } = this.selectionAnchor;
            const er = +endTd.getAttribute('data-row');
            const ec = +endTd.getAttribute('data-col');
            const minR = Math.min(ar, er), maxR = Math.max(ar, er);
            const minC = Math.min(ac, ec), maxC = Math.max(ac, ec);

            this.clearMultiSelection();
            const newSel = [];
            document.querySelectorAll('#sheet-area .ipcrf-preview-table td[data-cell]').forEach(td => {
                const r = +td.getAttribute('data-row');
                const c = +td.getAttribute('data-col');
                if (r >= minR && r <= maxR && c >= minC && c <= maxC) {
                    td.classList.add('multi-selected-cell');
                    newSel.push({ ref: td.getAttribute('data-cell'), td });
                }
            });
            this.selectedCells = newSel;
            /* detect single merged cell */
            if (newSel.length === 1) {
                const rs = +newSel[0].td.getAttribute('rowspan') || 1;
                const cs = +newSel[0].td.getAttribute('colspan') || 1;
                this.selectedMergedCell = (rs > 1 || cs > 1) ? newSel[0].ref : null;
            } else {
                this.selectedMergedCell = null;
            }
        },

        clearMultiSelection() {
            document.querySelectorAll('.multi-selected-cell').forEach(td => td.classList.remove('multi-selected-cell'));
            this.selectedCells     = [];
            this.selectedMergedCell = null;
        },

        isRectangularSelection() {
            if (this.selectedCells.length < 2) return false;
            const rows = new Set(this.selectedCells.map(c => c.td.getAttribute('data-row')));
            const cols = new Set(this.selectedCells.map(c => c.td.getAttribute('data-col')));
            return this.selectedCells.length === rows.size * cols.size;
        },

        /* ─── Header Click: Select Entire Column / Row + Resize Control ──── */
        clickColHeader(colIdx, hdrEl) {
            this.clearHdrSelection();
            this.clearMultiSelection();
            this.selectedCell = null;

            hdrEl.classList.add('hdr-selected');
            document.querySelectorAll(`#sheet-area [data-col="${colIdx}"]`).forEach(td => {
                if (!td.classList.contains('ipcrf-hdr-row') && !td.classList.contains('ipcrf-hdr-col'))
                    td.classList.add('col-highlighted');
            });

            const colEl = document.querySelector(`.ipcrf-preview-table colgroup col:nth-child(${colIdx + 1})`);
            this.activeHdrType = 'col';
            this.activeHdrIdx  = colIdx;
            this.activeHdrEl   = colEl;
            this.activeHdrSize = Math.round(colEl ? (parseFloat(colEl.style.width) || 80) : 80);
        },

        clickRowHeader(rowIdx, hdrEl) {
            this.clearHdrSelection();
            this.clearMultiSelection();
            this.selectedCell = null;

            hdrEl.classList.add('hdr-selected');
            const tr = this.findRowByIdx(rowIdx);
            if (tr) Array.from(tr.querySelectorAll('td')).forEach(td => {
                if (!td.classList.contains('ipcrf-hdr-row')) td.classList.add('row-highlighted');
            });

            this.activeHdrType = 'row';
            this.activeHdrIdx  = rowIdx;
            this.activeHdrEl   = tr;
            this.activeHdrSize = Math.round(tr ? (parseFloat(tr.style.height) || 20) : 20);
        },

        clearHdrSelection() {
            document.querySelectorAll('.ipcrf-hdr-col.hdr-selected, .ipcrf-hdr-row.hdr-selected').forEach(el => el.classList.remove('hdr-selected'));
            document.querySelectorAll('.col-highlighted').forEach(el => el.classList.remove('col-highlighted'));
            document.querySelectorAll('.row-highlighted').forEach(el => el.classList.remove('row-highlighted'));
            this.activeHdrType = null;
            this.activeHdrIdx  = null;
            this.activeHdrEl   = null;
        },

        applyHdrResize() {
            const size = Math.max(10, Math.min(800, parseInt(this.activeHdrSize) || 0));
            if (!size) { this.showToast('Enter a valid size (10–800 px)', 'info'); return; }

            if (this.activeHdrType === 'col') {
                const el = this.activeHdrEl || document.querySelector(`.ipcrf-preview-table colgroup col:nth-child(${this.activeHdrIdx + 1})`);
                if (el) el.style.width = size + 'px';
                this.showToast('Column width set to ' + size + 'px', 'success');
            } else if (this.activeHdrType === 'row') {
                const tr = this.activeHdrEl || this.findRowByIdx(this.activeHdrIdx);
                if (tr) tr.style.height = size + 'px';
                this.showToast('Row height set to ' + size + 'px', 'success');
            }
            this.activeHdrSize = size;
        },

        /* ─── Merge Cells ─────────────────────────────────────────────────── */
        mergeCells() {
            if (this.selectedCells.length < 2) { this.showToast('Select 2+ cells first', 'info'); return; }
            if (!this.isRectangularSelection()) { this.showToast('Selected cells must form a rectangle', 'info'); return; }
            this._pushHistory();

            const rows = this.selectedCells.map(c => +c.td.getAttribute('data-row'));
            const cols = this.selectedCells.map(c => +c.td.getAttribute('data-col'));
            const minR = Math.min(...rows), maxR = Math.max(...rows);
            const minC = Math.min(...cols), maxC = Math.max(...cols);
            const rowspan = maxR - minR + 1;
            const colspan = maxC - minC + 1;

            /* primary cell = top-left */
            const primary = this.selectedCells.find(c =>
                +c.td.getAttribute('data-row') === minR && +c.td.getAttribute('data-col') === minC
            );
            if (!primary) return;

            const { ref: pRef, td: pTd } = primary;

            /* collect and remove non-primary cells */
            const hiddenList = [];
            for (const { ref, td } of this.selectedCells) {
                if (ref === pRef) continue;
                hiddenList.push({
                    ref,
                    rowNum: +td.getAttribute('data-row'),
                    colNum: +td.getAttribute('data-col'),
                    text:   td.getAttribute('data-text') || '',
                    html:   td.innerHTML,
                });
                td.remove();
            }
            _hiddenCells[pRef] = { hiddenList, rowspan, colspan };

            pTd.setAttribute('rowspan', rowspan);
            pTd.setAttribute('colspan', colspan);

            /* highlight primary as selected */
            this.clearMultiSelection();
            document.querySelectorAll('.selected-cell').forEach(c => c.classList.remove('selected-cell'));
            pTd.classList.add('selected-cell');
            this.selectedCell      = pRef;
            this.selectedMergedCell = pRef;
            this.selectionAnchor   = null;

            const range = pRef + ':' + this.colIdxToLetter(maxC) + maxR;
            this.showToast(`Merged ${rowspan}×${colspan} — Save All to persist`, 'success');
            this._apiMerge(pRef, range, rowspan, colspan);
        },

        unmergeCells() {
            const pRef = this.selectedMergedCell || this.selectedCell;
            if (!pRef) { this.showToast('Select a merged cell first', 'info'); return; }
            this._pushHistory();
            const pTd = document.querySelector('[data-cell="' + pRef + '"]');
            if (!pTd) return;

            const stored = _hiddenCells[pRef];
            pTd.setAttribute('rowspan', '1');
            pTd.setAttribute('colspan', '1');

            if (stored && stored.hiddenList) {
                for (const hc of stored.hiddenList) {
                    const tr = this.findRowByIdx(hc.rowNum);
                    if (!tr) continue;
                    const newTd = document.createElement('td');
                    newTd.className = 'ipcrf-cell';
                    newTd.setAttribute('data-cell', hc.ref);
                    newTd.setAttribute('data-row',  hc.rowNum);
                    newTd.setAttribute('data-col',  hc.colNum);
                    newTd.setAttribute('data-text', hc.text);
                    newTd.style.cssText = 'padding:2px 4px;vertical-align:middle;cursor:pointer;white-space:nowrap;overflow:hidden;position:relative;';
                    newTd.innerHTML = hc.html;
                    /* insert at correct position by col */
                    const cells = Array.from(tr.querySelectorAll('td'));
                    const after = cells.find(td => (+td.getAttribute('data-col') || 0) > hc.colNum);
                    if (after) tr.insertBefore(newTd, after); else tr.appendChild(newTd);
                }
                delete _hiddenCells[pRef];
            }

            this.selectedMergedCell = null;
            this.clearMultiSelection();
            this.showToast('Cells unmerged — Save All to persist', 'success');
            this._apiUnmerge(pRef);
        },

        async _apiMerge(pRef, range, rowspan, colspan) {
            try {
                await fetch('/admin/templates/' + TEMPLATE_ID + '/merge-cells', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ action: 'merge', primary_ref: pRef, range, rowspan, colspan }),
                });
            } catch(e) { console.error('Merge persist error:', e); }
        },
        async _apiUnmerge(pRef) {
            try {
                await fetch('/admin/templates/' + TEMPLATE_ID + '/merge-cells', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ action: 'unmerge', primary_ref: pRef }),
                });
            } catch(e) { console.error('Unmerge persist error:', e); }
        },

        /* ─── Border Editing ──────────────────────────────────────────────── */
        applyBorder(side) {
            /* Collect target TDs: multi-selection, or single selected cell */
            const targets = this.selectedCells.length > 0
                ? this.selectedCells.map(c => c.td)
                : (this.selectedCell ? [document.querySelector('[data-cell="' + this.selectedCell + '"]')] : []);
            if (!targets.length) { this.showToast('Select cell(s) first', 'info'); return; }
            this._pushHistory();

            const SOLID = '1px solid #1e293b';   // visible border
            const NONE  = '1px solid transparent'; // invisible border (keeps layout stable)

            targets.forEach(td => {
                if (!td) return;
                switch (side) {
                    case 'all':
                        td.style.borderTop    = SOLID;
                        td.style.borderRight  = SOLID;
                        td.style.borderBottom = SOLID;
                        td.style.borderLeft   = SOLID;
                        break;
                    case 'top':
                        td.style.borderTop = SOLID;
                        break;
                    case 'bottom':
                        td.style.borderBottom = SOLID;
                        break;
                    case 'left':
                        td.style.borderLeft = SOLID;
                        break;
                    case 'right':
                        td.style.borderRight = SOLID;
                        break;
                    case 'none':
                        td.style.borderTop    = NONE;
                        td.style.borderRight  = NONE;
                        td.style.borderBottom = NONE;
                        td.style.borderLeft   = NONE;
                        break;
                }
            });

            const count = targets.length;
            const label = { all:'All Borders', top:'Border Top', bottom:'Border Bottom', left:'Border Left', right:'Border Right', none:'No Border' }[side];
            this.showToast(`${label} applied to ${count} cell${count > 1 ? 's' : ''}`, 'success');
        },

        /* ─── Text Formatting: Bold / Italic / Underline ──────────────────── */
        applyFormat(type) {
            const targets = this.selectedCells.length > 0
                ? this.selectedCells.map(c => c.td)
                : (this.selectedCell ? [document.querySelector('[data-cell="' + this.selectedCell + '"]')] : []);
            if (!targets.length) { this.showToast('Select cell(s) first', 'info'); return; }

            /* Determine if ALL targets already have the format — if so, toggle it OFF */
            const isAllActive = targets.every(td => {
                if (!td) return false;
                if (type === 'bold')      return td.style.fontWeight === 'bold' || td.style.fontWeight === '700';
                if (type === 'italic')    return td.style.fontStyle === 'italic';
                if (type === 'underline') return td.style.textDecoration.includes('underline');
                return false;
            });

            this._pushHistory();

            targets.forEach(td => {
                if (!td) return;
                if (type === 'bold') {
                    td.style.fontWeight = isAllActive ? '' : 'bold';
                } else if (type === 'italic') {
                    td.style.fontStyle = isAllActive ? '' : 'italic';
                } else if (type === 'underline') {
                    // Preserve existing line-through if any
                    if (isAllActive) {
                        td.style.textDecoration = td.style.textDecoration.replace('underline', '').trim() || '';
                    } else {
                        td.style.textDecoration = td.style.textDecoration
                            ? td.style.textDecoration + ' underline'
                            : 'underline';
                    }
                }
            });

            /* Update toolbar highlight state based on the first selected cell */
            const sample = targets[0];
            if (sample) {
                this._activeFmt = {
                    bold:      sample.style.fontWeight === 'bold' || sample.style.fontWeight === '700',
                    italic:    sample.style.fontStyle === 'italic',
                    underline: sample.style.textDecoration.includes('underline'),
                };
            }

            const label = { bold:'Bold', italic:'Italic', underline:'Underline' }[type];
            const state = isAllActive ? 'removed' : 'applied';
            this.showToast(`${label} ${state} on ${targets.length} cell${targets.length > 1 ? 's' : ''}`, 'success');
        },

        async applyAlignment(align) {
            const targets = this.selectedCells.length > 0
                ? this.selectedCells.map(c => c.td)
                : (this.selectedCell ? [document.querySelector('[data-cell="' + this.selectedCell + '"]')] : []);
            if (!targets.length) { this.showToast('Select cell(s) first', 'info'); return; }

            this._pushHistory();

            targets.forEach(td => {
                if (!td) return;
                td.style.textAlign = align;
            });

            for (const td of targets) {
                if (!td) continue;
                const cellRef = td.getAttribute('data-cell');
                if (cellRef) {
                    try {
                        await fetch('/admin/templates/' + TEMPLATE_ID + '/cell-align', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify({ cell_ref: cellRef, align }),
                        });
                    } catch (e) {
                        console.error('Failed to save alignment:', e);
                    }
                }
            }

            this.showToast(`Alignment set to ${align}`, 'success');
        },

        /* ─── Row/Column Add-Delete ───────────────────────────────────────── */
        promptAddRow(dir) {
            const ref = this.selectedCell;
            if (!ref) { this.showToast('Select a cell to identify the target row', 'info'); return; }
            const m = ref.match(/(\d+)$/);
            if (m) this.addRowAt(+m[1], dir);
        },
        promptDeleteRow() {
            const ref = this.selectedCell;
            if (!ref) { this.showToast('Select a cell to identify the row', 'info'); return; }
            const m = ref.match(/(\d+)$/);
            if (m) this.deleteRowAt(+m[1]);
        },
        promptAddCol(dir) {
            const ref = this.selectedCell;
            if (!ref) { this.showToast('Select a cell to identify the target column', 'info'); return; }
            const m = ref.match(/^([A-Z]+)/);
            if (m) this.addColAt(this.colLetterToIdx(m[1]), dir);
        },
        promptDeleteCol() {
            const ref = this.selectedCell;
            if (!ref) { this.showToast('Select a cell to identify the column', 'info'); return; }
            const m = ref.match(/^([A-Z]+)/);
            if (m) this.deleteColAt(this.colLetterToIdx(m[1]));
        },

        addRowAt(rowIdx, dir) {
            const tbody    = document.querySelector('.ipcrf-preview-table tbody');
            if (!tbody) return;
            this._pushHistory();
            const totalCols = document.querySelectorAll('.ipcrf-hdr-col').length;
            const label    = dir === 'below' ? rowIdx + 0.5 : rowIdx - 0.5;
            const newTr    = this._buildNewRow(label, totalCols);
            const refTr    = this._findRowTr(rowIdx);
            if (dir === 'below' && refTr) tbody.insertBefore(newTr, refTr.nextSibling);
            else if (refTr)               tbody.insertBefore(newTr, refTr);
            else                          tbody.appendChild(newTr);
            this.hideCtxMenu();
            this.showToast('Row added — Save All to persist', 'success');
        },
        deleteRowAt(rowIdx) {
            const tr = this._findRowTr(rowIdx);
            if (tr) { this._pushHistory(); tr.remove(); }
            this.hideCtxMenu();
            this.showToast('Row deleted — Save All to persist', 'success');
        },

        addColAt(colIdx, dir) {
            const table = document.querySelector('.ipcrf-preview-table');
            if (!table) return;
            this._pushHistory();
            const domIdx = dir === 'right' ? colIdx + 1 : colIdx;
            const newLetter = this.colIdxToLetter(domIdx);

            /* colgroup */
            const colgroup = table.querySelector('colgroup');
            const refCol   = colgroup.querySelectorAll('col')[domIdx] || null;
            const newCol   = document.createElement('col');
            newCol.style.width = '80px';
            colgroup.insertBefore(newCol, refCol);

            /* each row */
            Array.from(table.querySelectorAll('tbody tr')).forEach((tr, ti) => {
                const cells  = Array.from(tr.querySelectorAll('td'));
                const refCell = cells[domIdx] || null;
                if (ti === 0) {
                    /* header */
                    const th = document.createElement('td');
                    th.className = 'ipcrf-hdr-col';
                    th.setAttribute('data-col-idx', domIdx);
                    th.innerHTML = newLetter + '<div class="col-resizer"></div>';
                    tr.insertBefore(th, refCell);
                } else {
                    const hdr  = tr.querySelector('.ipcrf-hdr-row');
                    const rNum = hdr ? hdr.getAttribute('data-row-idx') : ti;
                    const td   = document.createElement('td');
                    td.className = 'ipcrf-cell';
                    td.setAttribute('data-cell', newLetter + rNum);
                    td.setAttribute('data-row',  rNum);
                    td.setAttribute('data-col',  domIdx);
                    td.setAttribute('data-text', '');
                    td.style.cssText = 'padding:2px 4px;vertical-align:middle;cursor:pointer;white-space:nowrap;overflow:hidden;position:relative;';
                    tr.insertBefore(td, refCell);
                }
            });

            this.hideCtxMenu();
            setTimeout(() => { if (typeof initSpreadsheetResizers === 'function') initSpreadsheetResizers(); }, 60);
            this.showToast('Column added — Save All to persist', 'success');
        },
        deleteColAt(colIdx) {
            const table = document.querySelector('.ipcrf-preview-table');
            if (!table) return;
            this._pushHistory();
            const cols = table.querySelectorAll('colgroup col');
            if (cols[colIdx]) cols[colIdx].remove();
            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells[colIdx]) cells[colIdx].remove();
            });
            this.hideCtxMenu();
            this.showToast('Column deleted — Save All to persist', 'success');
        },

        addCell(direction) {
            const ref = this.selectedCell;
            if (!ref) { this.showToast('Select a cell first', 'info'); return; }

            const td = document.querySelector('[data-cell="' + ref + '"]');
            if (!td) return;

            const tr = td.closest('tr');
            if (!tr) return;

            this._pushHistory();

            const curRow = parseInt(td.getAttribute('data-row'));
            const curCol = parseInt(td.getAttribute('data-col'));

            // Insertion index determines where the new TD goes:
            // if direction is 'left', it goes before 'td' (same column index)
            // if direction is 'right', it goes after 'td' (column index + 1)
            const insertColIdx = direction === 'left' ? curCol : curCol + 1;

            // 1. Increment column index of all TDs in this row that are at or to the right of the insertion point
            const rowCells = Array.from(tr.querySelectorAll('td.ipcrf-cell'));
            rowCells.forEach(cell => {
                const cIdx = parseInt(cell.getAttribute('data-col'));
                if (cIdx >= insertColIdx) {
                    const newColIdx = cIdx + 1;
                    const newLetter = this.colIdxToLetter(newColIdx);
                    const oldCellRef = cell.getAttribute('data-cell');
                    const newCellRef = newLetter + curRow;
                    
                    cell.setAttribute('data-col', newColIdx);
                    cell.setAttribute('data-cell', newCellRef);
                    
                    // Update any mapped fields
                    this.mappedFields.forEach(f => {
                        if (f.cell_ref === oldCellRef) {
                            f.cell_ref = newCellRef;
                        }
                    });
                    
                    // Rename the badge text if mapped
                    const badge = cell.querySelector('.field-badge');
                    if (badge) {
                        const existing = this.mappedFields.find(f => f.cell_ref === newCellRef);
                        if (existing) {
                            badge.innerHTML = '<i class="fas ' + this.fieldTypeIcon(existing.field_type) + '" style="font-size:8px;"></i> ' + (existing.field_label || existing.field_type).substring(0, 20);
                        }
                    }
                }
            });

            // 2. Create the new TD
            const newTd = document.createElement('td');
            newTd.className = 'ipcrf-cell';
            const newLetter = this.colIdxToLetter(insertColIdx);
            newTd.setAttribute('data-cell', newLetter + curRow);
            newTd.setAttribute('data-row', curRow);
            newTd.setAttribute('data-col', insertColIdx);
            newTd.setAttribute('data-text', '');
            newTd.style.cssText = 'padding:2px 4px;vertical-align:middle;cursor:pointer;white-space:nowrap;overflow:hidden;position:relative;';

            // 3. Insert the new TD in the DOM
            const nextCell = tr.querySelector(`td.ipcrf-cell[data-col="${insertColIdx + 1}"]`);
            if (nextCell) {
                tr.insertBefore(newTd, nextCell);
            } else {
                tr.appendChild(newTd);
            }

            // 4. Update the template total_cols if this row exceeds current total_cols
            const totalCellsInRow = tr.querySelectorAll('td.ipcrf-cell').length;
            const currentTotalCols = document.querySelectorAll('.ipcrf-hdr-col').length;
            if (totalCellsInRow > currentTotalCols) {
                const hdrTr = document.querySelector('.ipcrf-preview-table tbody tr');
                if (hdrTr) {
                    const nextHdr = this.colIdxToLetter(totalCellsInRow);
                    const th = document.createElement('td');
                    th.className = 'ipcrf-hdr-col';
                    th.setAttribute('data-col-idx', totalCellsInRow);
                    th.innerHTML = nextHdr + '<div class="col-resizer"></div>';
                    hdrTr.appendChild(th);
                }
                const colgroup = document.querySelector('.ipcrf-preview-table colgroup');
                if (colgroup) {
                    const newCol = document.createElement('col');
                    newCol.style.width = '80px';
                    colgroup.appendChild(newCol);
                }
                setTimeout(() => { if (typeof initSpreadsheetResizers === 'function') initSpreadsheetResizers(); }, 60);
            }

            this.hideCtxMenu();
            this.selectedCell = null;
            this.showToast('Cell inserted — Save All to persist', 'success');
        },

        serializeSheetData() {
            const rows = [];
            const trs = Array.from(document.querySelectorAll('.ipcrf-preview-table tbody tr')).slice(1);
            
            trs.forEach((tr) => {
                const rowCells = [];
                const tds = Array.from(tr.querySelectorAll('td')).slice(1);
                
                tds.forEach((td) => {
                    const cellRef = td.getAttribute('data-cell') || '';
                    const rowNum = td.getAttribute('data-row') || '1';
                    const colNum = td.getAttribute('data-col') || '1';
                    const val = td.getAttribute('data-text') || '';
                    const rowspan = td.getAttribute('rowspan') ? parseInt(td.getAttribute('rowspan')) : 1;
                    const colspan = td.getAttribute('colspan') ? parseInt(td.getAttribute('colspan')) : 1;
                    
                    const style = {};
                    const styleAttr = td.getAttribute('style') || '';
                    
                    if (styleAttr.includes('font-weight:bold')) style.bold = true;
                    if (styleAttr.includes('font-style:italic')) style.italic = true;
                    if (styleAttr.includes('text-decoration:underline')) style.underline = true;
                    if (styleAttr.includes('text-align:center')) style.h_align = 'center';
                    else if (styleAttr.includes('text-align:right')) style.h_align = 'right';
                    else if (styleAttr.includes('text-align:left')) style.h_align = 'left';
                    
                    const bgMatch = styleAttr.match(/background-color:\s*(#[0-9a-fA-F]+)/);
                    if (bgMatch) style.bg_color = bgMatch[1];
                    
                    const sizeMatch = styleAttr.match(/font-size:\s*([0-9.]+)pt/);
                    if (sizeMatch) style.font_size = parseFloat(sizeMatch[1]);
                    
                    const drawings = [];
                    td.querySelectorAll('img').forEach(img => {
                        drawings.push({
                            url: img.src,
                            width: parseInt(img.style.width) || 120,
                            height: parseInt(img.style.height) || 60,
                            offsetX: parseInt(img.style.left) || 0,
                            offsetY: parseInt(img.style.top) || 0,
                        });
                    });
                    
                    rowCells.push({
                        cell_ref: cellRef,
                        row: parseInt(rowNum),
                        col: parseInt(colNum),
                        value: val,
                        raw_value: val,
                        rowspan: rowspan,
                        colspan: colspan,
                        hidden: td.classList.contains('hidden') || td.style.display === 'none',
                        style: style,
                        drawings: drawings.length > 0 ? drawings : undefined
                    });
                });
                rows.push(rowCells);
            });
            return rows;
        },

        _buildNewRow(label, totalCols) {
            const tr = document.createElement('tr');
            tr.style.height = '20px';
            const th = document.createElement('td');
            th.className = 'ipcrf-hdr-row';
            th.setAttribute('data-row-idx', label);
            th.innerHTML = label + '<div class="row-resizer"></div>';
            tr.appendChild(th);
            for (let c = 1; c <= totalCols; c++) {
                const col = this.colIdxToLetter(c);
                const td  = document.createElement('td');
                td.className = 'ipcrf-cell';
                td.setAttribute('data-cell', col + label);
                td.setAttribute('data-row',  label);
                td.setAttribute('data-col',  c);
                td.setAttribute('data-text', '');
                td.style.cssText = 'padding:2px 4px;vertical-align:middle;cursor:pointer;white-space:nowrap;overflow:hidden;position:relative;';
                tr.appendChild(td);
            }
            return tr;
        },

        _findRowTr(rowIdx) {
            for (const tr of document.querySelectorAll('.ipcrf-preview-table tbody tr')) {
                const hdr = tr.querySelector('.ipcrf-hdr-row');
                if (hdr && +hdr.getAttribute('data-row-idx') === rowIdx) return tr;
            }
            return null;
        },

        findRowByIdx(idx) { return this._findRowTr(idx); },

        /* ─── Letter ↔ Index helpers ──────────────────────────────────────── */
        colLetterToIdx(s) {
            let n = 0;
            for (let i = 0; i < s.length; i++) n = n * 26 + s.charCodeAt(i) - 64;
            return n;
        },
        colIdxToLetter(n) {
            let s = '';
            while (n > 0) { s = String.fromCharCode(64 + (n % 26 || 26)) + s; n = Math.floor((n - 1) / 26); }
            return s;
        },

        /* ─── Single Cell Selection ───────────────────────────────────────── */
        selectCell(ref, el) {
            document.querySelectorAll('.selected-cell').forEach(c => c.classList.remove('selected-cell'));
            el.classList.add('selected-cell');
            this.selectedCell = ref;
            this.tab = 'mapper';
            const existing = this.mappedFields.find(f => f.cell_ref === ref);
            if (existing) {
                this.currentFieldType       = existing.field_type;
                this.currentFieldLabel      = existing.field_label || '';
                this.currentRequired        = existing.is_required || false;
                this.currentDropdownOptions = (existing.field_type === 'dropdown' && existing.field_options) ? existing.field_options.join('\n') : '';
                this.currentMeanTargets     = (existing.field_type === 'calculated_mean' && existing.field_options) ? (existing.field_options.targets || '') : '';
            } else {
                this.currentFieldType       = 'text';
                this.currentFieldLabel      = '';
                this.currentRequired        = false;
                this.currentDropdownOptions = '';
                this.currentMeanTargets     = '';
            }
        },

        selectCellFromField(ref) {
            const td = document.querySelector('[data-cell="' + ref + '"]');
            if (td) { this.tab = 'mapper'; this.selectCell(ref, td); }
        },

        /* ─── Inline Text Edit ───────────────────────────────────────────── */
        startInlineEdit(ref, td) {
            if (this._editingRef) return;
            if (td.querySelector('.field-badge')) { this.showToast('Remove the field mapping first to edit text', 'info'); return; }
            this._editingRef = ref;
            td.classList.add('editing-cell');
            const originalText = td.getAttribute('data-text') || '';
            const originalHTML = td.innerHTML;
            const clonedImgs   = Array.from(td.querySelectorAll('img')).map(i => i.cloneNode(true));
            const input = document.createElement('input');
            input.className = 'cell-inline-editor';
            input.value = originalText;
            td.innerHTML = '';
            clonedImgs.forEach(img => td.appendChild(img));
            td.appendChild(input);
            input.focus(); input.select();
            const commit = async (save) => {
                if (this._editingRef !== ref) return;
                this._editingRef = null;
                td.classList.remove('editing-cell');
                const newVal = input.value;
                if (save && newVal !== originalText) {
                    this._pushHistory();   // snapshot before mutating DOM
                    td.setAttribute('data-text', newVal);
                    td.innerHTML = '';
                    clonedImgs.forEach(img => td.appendChild(img.cloneNode(true)));
                    if (newVal) td.appendChild(document.createTextNode(newVal));
                    this.showToast('Cell text updated', 'success');
                    await this._saveCellText(ref, newVal);
                } else {
                    td.innerHTML = originalHTML;
                }
            };
            input.addEventListener('blur',    ()  => commit(true));
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter')  { e.preventDefault(); commit(true);  }
                if (e.key === 'Escape') { e.preventDefault(); commit(false); }
            });
        },

        async _saveCellText(ref, value) {
            try {
                await fetch('/admin/templates/' + TEMPLATE_ID + '/cell-text', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ cell_ref: ref, value }),
                });
            } catch(e) { console.error(e); }
        },

        /* ─── Undo / Redo ─────────────────────────────────────────────────── */
        _snapshotState() {
            const table = document.querySelector('.ipcrf-preview-table');
            return {
                tableHTML:    table ? table.outerHTML : '',
                mappedFields: JSON.parse(JSON.stringify(this.mappedFields)),
                hiddenCells:  JSON.parse(JSON.stringify(_hiddenCells)),
            };
        },

        _pushHistory() {
            /* Trim any future states (branching) */
            if (this._historyIdx < this._history.length - 1) {
                this._history.splice(this._historyIdx + 1);
            }
            this._history.push(this._snapshotState());
            this._historyIdx = this._history.length - 1;
            /* Cap at 50 snapshots to avoid memory bloat */
            if (this._history.length > 50) {
                this._history.shift();
                this._historyIdx = this._history.length - 1;
            }
        },

        _restoreState(state) {
            const container = document.getElementById('sheet-area');
            if (!container || !state || !state.tableHTML) return;
            /* Replace table HTML */
            container.innerHTML = state.tableHTML;
            /* Restore Alpine-reactive state */
            this.mappedFields = JSON.parse(JSON.stringify(state.mappedFields));
            /* Restore _hiddenCells global */
            Object.keys(_hiddenCells).forEach(k => delete _hiddenCells[k]);
            Object.assign(_hiddenCells, JSON.parse(JSON.stringify(state.hiddenCells)));
            /* Clear selection */
            this.selectedCell       = null;
            this.selectedCells      = [];
            this.selectedMergedCell = null;
            this.selectionAnchor    = null;
            this.activeHdrType      = null;
            /* Reinit drag-resizers (they use direct listeners, not delegation) */
            setTimeout(() => {
                if (typeof initSpreadsheetResizers === 'function') initSpreadsheetResizers();
                this.refreshCellHighlights();
            }, 30);
        },

        undo() {
            if (this._historyIdx <= 0) { this.showToast('Nothing to undo', 'info'); return; }
            this._historyIdx--;
            this._restoreState(this._history[this._historyIdx]);
            this.showToast('Undo ←', 'info');
        },

        redo() {
            if (this._historyIdx >= this._history.length - 1) { this.showToast('Nothing to redo', 'info'); return; }
            this._historyIdx++;
            this._restoreState(this._history[this._historyIdx]);
            this.showToast('Redo →', 'info');
        },

        /* ─── Picture Upload ─────────────────────────────────────────────── */
        triggerPictureUpload() {
            if (!this.selectedCell) { this.showToast('Select a cell first', 'info'); return; }
            document.getElementById('picture-file-input').click();
        },
        async handlePictureUpload(e) {
            const file = e.target.files[0];
            if (!file || !this.selectedCell) return;
            const fd = new FormData();
            fd.append('image', file); fd.append('cell_ref', this.selectedCell);
            this.showToast('Uploading image…', 'info');
            try {
                const r = await fetch('/admin/templates/' + TEMPLATE_ID + '/upload-image', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const d = await r.json();
                if (d.success) {
                    const td = document.querySelector('[data-cell="' + this.selectedCell + '"]');
                    if (td) {
                        const img = document.createElement('img');
                        img.src = d.url;
                        img.style.cssText = 'max-width:100%;max-height:70px;object-fit:contain;display:block;pointer-events:none;';
                        td.innerHTML = ''; td.appendChild(img); td.setAttribute('data-text', '');
                    }
                    this.currentFieldType  = 'picture';
                    this.currentFieldLabel = this.currentFieldLabel || 'Image';
                    this.assignField();
                    this.showToast('Image uploaded & assigned!', 'success');
                } else { this.showToast(d.message || 'Upload failed', 'error'); }
            } catch { this.showToast('Upload failed', 'error'); }
            e.target.value = '';
        },

        /* ─── Field Assignment ───────────────────────────────────────────── */
        assignField() {
            if (!this.selectedCell) return;
            let opts = null;
            if (this.currentFieldType === 'dropdown') {
                opts = this.currentDropdownOptions
                    ? this.currentDropdownOptions.split('\n').map(s => s.trim()).filter(Boolean)
                    : null;
            } else if (this.currentFieldType === 'calculated_mean') {
                opts = { targets: this.currentMeanTargets || '' };
            }
            const field = { cell_ref: this.selectedCell, field_type: this.currentFieldType, field_label: this.currentFieldLabel, is_required: this.currentRequired, field_options: opts };
            const idx = this.mappedFields.findIndex(f => f.cell_ref === this.selectedCell);
            if (idx >= 0) this.mappedFields[idx] = field; else this.mappedFields.push(field);
            this.refreshCellHighlights();
            this.showToast('Field assigned to ' + this.selectedCell, 'success');
        },
        removeField(ref) {
            this.mappedFields = this.mappedFields.filter(f => f.cell_ref !== ref);
            const td = document.querySelector('[data-cell="' + ref + '"]');
            if (td) { td.classList.remove('mapped-cell'); const b = td.querySelector('.field-badge'); if (b) b.remove(); }
            if (this.selectedCell === ref) this.selectedCell = null;
        },
        isCellMapped(ref) { return !!this.mappedFields.find(f => f.cell_ref === ref); },

        refreshCellHighlights() {
            document.querySelectorAll('.ipcrf-preview-table td').forEach(td => {
                td.classList.remove('mapped-cell');
                const b = td.querySelector('.field-badge'); if (b) b.remove();
            });
            this.mappedFields.forEach(f => {
                const td = document.querySelector('[data-cell="' + f.cell_ref + '"]');
                if (!td) return;
                td.classList.add('mapped-cell');
                const badge = document.createElement('span');
                badge.className = 'field-badge ' + f.field_type;
                badge.innerHTML = '<i class="fas ' + this.fieldTypeIcon(f.field_type) + '" style="font-size:8px;"></i> ' + (f.field_label || f.field_type).substring(0, 20);
                const imgs = Array.from(td.querySelectorAll('img'));
                td.innerHTML = '';
                imgs.forEach(img => td.appendChild(img));
                td.appendChild(badge);
            });
        },

        /* ─── Icons & Labels ─────────────────────────────────────────────── */
        fieldTypeIcon(t) {
            return { autofill_name:'fa-user', autofill_position:'fa-briefcase', autofill_department:'fa-building', autofill_date:'fa-calendar-day', autofill_division_chief:'fa-user-tie', autofill_division_chief_position:'fa-briefcase', autofill_division_chief_signature:'fa-signature', autofill_approving_authority:'fa-stamp', autofill_approving_authority_position:'fa-briefcase', autofill_approving_authority_signature:'fa-signature', date:'fa-calendar-alt', text:'fa-font', number:'fa-hashtag', textarea:'fa-align-left', rating:'fa-star', dropdown:'fa-chevron-down', signature:'fa-signature', readonly:'fa-lock', picture:'fa-image', calculated_mean:'fa-calculator' }[t] || 'fa-square';
        },
        fieldTypeLabel(t) {
            return { autofill_name:'Auto-Fill: Employee Name', autofill_position:'Auto-Fill: Position', autofill_department:'Auto-Fill: Department', autofill_date:'Auto-Fill: Date Signed', autofill_division_chief:'Auto-Fill: Division Chief Name', autofill_division_chief_position:'Auto-Fill: Div. Chief Position', autofill_division_chief_signature:'Auto-Fill: Div. Chief Signature', autofill_approving_authority:'Auto-Fill: Approving Authority Name', autofill_approving_authority_position:'Auto-Fill: Appr. Auth. Position', autofill_approving_authority_signature:'Auto-Fill: Appr. Auth. Signature', date:'Date Picker', text:'Text Input', number:'Number Input', textarea:'Text Area', rating:'Rating', dropdown:'Dropdown', signature:'Signature', readonly:'Read-Only', picture:'Embedded Image', calculated_mean:'Calculated Mean' }[t] || t;
        },

        /* ─── Context Menu ───────────────────────────────────────────────── */
        showCtxMenu(x, y, type, index) {
            const sx = Math.min(x, window.innerWidth  - 210);
            const sy = Math.min(y, window.innerHeight - 140);
            this.ctxMenu = { show: true, x: sx, y: sy, type, index };
        },
        hideCtxMenu() { this.ctxMenu.show = false; },

        /* ─── Save ───────────────────────────────────────────────────────── */
        async saveAll() {
            this.saving = true;
            try {
                // 1. Save Layout
                const sheetRows = this.serializeSheetData();
                const layoutRes = await fetch('/admin/templates/' + TEMPLATE_ID + '/save-layout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ rows: sheetRows }),
                });
                const layoutData = await layoutRes.json();
                if (!layoutData.success) {
                    this.showToast(layoutData.message || 'Layout save error', 'error');
                    this.saving = false;
                    return;
                }

                // 2. Save Fields
                const r = await fetch('/admin/templates/' + TEMPLATE_ID + '/fields', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ fields: this.mappedFields }),
                });
                const d = await r.json();
                if (d.success) this.showToast('All fields and layout saved!', 'success');
                else           this.showToast(d.message || 'Save error', 'error');
            } catch { this.showToast('Network error', 'error'); }
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
            } catch { this.showToast('Network error', 'error'); }
        },

        /* ─── Toast ──────────────────────────────────────────────────────── */
        showToast(msg, type = 'success') {
            this.toast = { message: msg, type };
            const el = document.getElementById('toast');
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 3500);
        },
    };
}

/* ── Spreadsheet Resizers (drag-handles on edges) ─────────────────────── */
function initSpreadsheetResizers() {
    document.querySelectorAll('.col-resizer').forEach(rz => {
        rz.addEventListener('mousedown', e => {
            e.preventDefault(); e.stopPropagation();
            rz.classList.add('dragging');
            const sx  = e.clientX;
            const th  = rz.closest('.ipcrf-hdr-col'); if (!th) return;
            const ci  = +th.getAttribute('data-col-idx');
            const col = document.querySelector(`.ipcrf-preview-table colgroup col:nth-child(${ci + 1})`); if (!col) return;
            const sw  = parseFloat(col.style.width) || 80;
            const mv  = me => col.style.width = Math.max(20, sw + me.clientX - sx) + 'px';
            const up  = ()  => { rz.classList.remove('dragging'); document.removeEventListener('mousemove', mv); document.removeEventListener('mouseup', up); };
            document.addEventListener('mousemove', mv); document.addEventListener('mouseup', up);
        });
    });
    document.querySelectorAll('.row-resizer').forEach(rz => {
        rz.addEventListener('mousedown', e => {
            e.preventDefault(); e.stopPropagation();
            rz.classList.add('dragging');
            const sy  = e.clientY;
            const tr  = rz.closest('tr'); if (!tr) return;
            const sh  = tr.getBoundingClientRect().height;
            const mv  = me => tr.style.height = Math.max(14, sh + me.clientY - sy) + 'px';
            const up  = ()  => { rz.classList.remove('dragging'); document.removeEventListener('mousemove', mv); document.removeEventListener('mouseup', up); };
            document.addEventListener('mousemove', mv); document.addEventListener('mouseup', up);
        });
    });
}
</script>
@endpush
