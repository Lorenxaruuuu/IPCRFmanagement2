@extends('admin.layouts.admin')
@section('title', 'IPCRF Admin — Management System')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
*{box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#0f172a;margin:0;}

/* ── Layout ─────────────────────────────── */
.layout{display:flex;height:100vh;overflow:hidden;}
.sidebar{width:260px;flex-shrink:0;background:linear-gradient(180deg,#1e293b 0%,#0f172a 100%);display:flex;flex-direction:column;border-right:1px solid rgba(255,255,255,.06);}
.main-area{flex:1;display:flex;flex-direction:column;overflow:hidden;}
.topbar{background:rgba(15,23,42,.9);backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,.06);padding:0 28px;height:64px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.content-area{flex:1;overflow-y:auto;padding:28px;background:#0f172a;}

/* ── Sidebar ─────────────────────────────── */
.sb-brand{padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,.06);}
.sb-brand h1{font-size:16px;font-weight:700;color:#f1f5f9;margin:0;}
.sb-brand p{font-size:11px;color:#64748b;margin:2px 0 0;}
.sb-nav{flex:1;padding:12px 0;overflow-y:auto;}
.sb-section{font-size:10px;font-weight:600;color:#475569;letter-spacing:.08em;text-transform:uppercase;padding:16px 20px 6px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;font-size:13px;font-weight:500;color:#94a3b8;cursor:pointer;transition:all .2s;border-left:3px solid transparent;text-decoration:none;}
.nav-item:hover{color:#f1f5f9;background:rgba(255,255,255,.05);border-left-color:rgba(99,102,241,.4);}
.nav-item.active{color:#fff;background:rgba(99,102,241,.15);border-left-color:#6366f1;}
.nav-item i{width:18px;text-align:center;font-size:14px;}
.sb-badge{margin-left:auto;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;}
.sb-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.06);}

/* ── Cards ─────────────────────────────── */
.stat-card{background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:22px;transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(0,0,0,.3);}
.stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.stat-val{font-size:30px;font-weight:800;color:#f1f5f9;}
.stat-lbl{font-size:12px;color:#64748b;margin-top:2px;}

/* ── Section Panels ─────────────────────── */
.panel{background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:24px;}
.panel-title{font-size:16px;font-weight:700;color:#f1f5f9;margin:0 0 4px;}
.panel-sub{font-size:12px;color:#64748b;margin:0 0 20px;}

/* ── Buttons ─────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .2s;text-decoration:none;}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;}
.btn-primary:hover{opacity:.9;transform:translateY(-1px);box-shadow:0 4px 20px rgba(99,102,241,.4);}
.btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-success:hover{opacity:.9;}
.btn-danger{background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);}
.btn-danger:hover{background:rgba(239,68,68,.25);}
.btn-ghost{background:rgba(255,255,255,.05);color:#94a3b8;border:1px solid rgba(255,255,255,.1);}
.btn-ghost:hover{background:rgba(255,255,255,.1);color:#f1f5f9;}

/* ── Badges ─────────────────────────────── */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-draft{background:rgba(100,116,139,.2);color:#94a3b8;}
.badge-submitted{background:rgba(59,130,246,.2);color:#60a5fa;}
.badge-review{background:rgba(245,158,11,.2);color:#fbbf24;}
.badge-approved{background:rgba(16,185,129,.2);color:#34d399;}
.badge-rejected{background:rgba(239,68,68,.2);color:#f87171;}

/* ── Tables ─────────────────────────────── */
.data-table{width:100%;border-collapse:collapse;}
.data-table th{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06);text-align:left;}
.data-table td{font-size:13px;color:#cbd5e1;padding:12px;border-bottom:1px solid rgba(255,255,255,.04);}
.data-table tr:hover td{background:rgba(255,255,255,.03);}
.data-table tr:last-child td{border-bottom:none;}

/* ── Forms ─────────────────────────────── */
.form-label{display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:6px;}
.form-input{width:100%;background:rgba(15,23,42,.8);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:10px 14px;font-size:13px;color:#f1f5f9;outline:none;transition:border-color .2s;}
.form-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15);}
.form-input::placeholder{color:#475569;}
select.form-input option{background:#1e293b;color:#f1f5f9;}

/* ── Upload zone ────────────────────────── */
.upload-zone{border:2px dashed rgba(99,102,241,.3);border-radius:16px;padding:48px 24px;text-align:center;cursor:pointer;transition:all .3s;background:rgba(99,102,241,.04);}
.upload-zone:hover,.upload-zone.dragover{border-color:#6366f1;background:rgba(99,102,241,.08);}

/* ── Template builder ───────────────────── */
.builder-wrap{display:flex;gap:0;height:calc(100vh - 100px);overflow:hidden;background:rgba(15,23,42,.5);border:1px solid rgba(255,255,255,.07);border-radius:16px;}
.spreadsheet-area{flex:1;overflow:auto;padding:0;}
.field-panel{width:300px;flex-shrink:0;background:rgba(30,41,59,.9);border-left:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;overflow-y:auto;}
.ipcrf-preview-table{border-collapse:collapse;min-width:100%;}
.ipcrf-preview-table td{border:1px solid #334155;font-size:11px;cursor:pointer;min-width:60px;max-width:200px;}
.ipcrf-preview-table td:hover{background:rgba(99,102,241,.1) !important;outline:2px solid #6366f1;}
.ipcrf-preview-table td.selected{outline:2px solid #f59e0b !important;background:rgba(245,158,11,.1) !important;}
.ipcrf-preview-table td.ipcrf-cell--mapped{outline:2px solid rgba(99,102,241,.5);}
.field-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;padding:2px 6px;border-radius:4px;background:rgba(99,102,241,.25);color:#a5b4fc;white-space:nowrap;max-width:100%;overflow:hidden;}
.field-badge.field-badge--autofill_name,.field-badge.field-badge--autofill_position,.field-badge.field-badge--autofill_department,.field-badge.field-badge--autofill_date{background:rgba(16,185,129,.2);color:#6ee7b7;}
.field-badge.field-badge--readonly{background:rgba(100,116,139,.2);color:#94a3b8;}
.field-badge.field-badge--dropdown{background:rgba(245,158,11,.2);color:#fbbf24;}
.field-badge.field-badge--signature{background:rgba(236,72,153,.2);color:#f9a8d4;}

/* ── Field type selector ────────────────── */
.ft-btn{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;font-size:12px;cursor:pointer;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:#94a3b8;transition:all .2s;text-align:left;width:100%;}
.ft-btn:hover,.ft-btn.selected{background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.4);color:#a5b4fc;}

/* ── Modals ─────────────────────────────── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:1000;display:flex;align-items:center;justify-content:center;}
.modal-box{background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:32px;min-width:480px;max-width:90vw;max-height:90vh;overflow-y:auto;}
.modal-title{font-size:18px;font-weight:700;color:#f1f5f9;margin:0 0 20px;}

/* ── Tabs ───────────────────────────────── */
.tab-bar{display:flex;gap:2px;background:rgba(15,23,42,.5);border-radius:12px;padding:4px;margin-bottom:24px;}
.tab-btn{flex:1;padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .2s;}
.tab-btn.active{background:rgba(99,102,241,.2);color:#a5b4fc;}

/* ── Misc ─────────────────────────────── */
.view-section{display:none;animation:fadeIn .25s ease;}
.view-section.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
.avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;}
.divider{border:none;border-top:1px solid rgba(255,255,255,.06);margin:20px 0;}
.search-input{background:rgba(15,23,42,.6);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:8px 14px 8px 38px;font-size:13px;color:#f1f5f9;outline:none;width:100%;}
.search-wrap{position:relative;}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#475569;font-size:13px;}
.empty-state{text-align:center;padding:60px 24px;color:#475569;}
.empty-state i{font-size:40px;margin-bottom:16px;display:block;}
.empty-state p{font-size:14px;}
.toast{position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:14px 20px;font-size:13px;color:#f1f5f9;z-index:9999;transform:translateY(20px);opacity:0;transition:all .3s;min-width:280px;display:flex;align-items:center;gap:10px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast.toast-success{border-left:3px solid #10b981;}
.toast.toast-error{border-left:3px solid #ef4444;}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
@media(max-width:768px){.grid-2,.grid-3,.grid-4{grid-template-columns:1fr;}.sidebar{display:none;}}
</style>
@endpush

@section('content')
<div class="layout" x-data="adminApp()">
    {{-- ═══ SIDEBAR ═══════════════════════════════════════════════════════════ --}}
    <aside class="sidebar">
        <div class="sb-brand">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <div style="width:32px;height:32px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-file-contract" style="color:#fff;font-size:14px;"></i>
                </div>
                <div>
                    <h1>IPCRF Admin</h1>
                    <p>Management System</p>
                </div>
            </div>
        </div>

        <nav class="sb-nav">
            <span class="sb-section">Overview</span>
            <a class="nav-item" :class="{active: view==='dashboard'}" @click.prevent="showView('dashboard')" href="#">
                <i class="fas fa-home"></i> Dashboard
            </a>

            <span class="sb-section">Template System</span>
            <a class="nav-item" :class="{active: view==='templates'}" @click.prevent="showView('templates')" href="#">
                <i class="fas fa-file-excel"></i> IPCRF Templates
            </a>
            <a class="nav-item" :class="{active: view==='positions'}" @click.prevent="showView('positions')" href="#">
                <i class="fas fa-id-badge"></i> Positions
            </a>

            <span class="sb-section">Submissions</span>
            <a class="nav-item" :class="{active: view==='submissions'}" @click.prevent="showView('submissions')" href="#">
                <i class="fas fa-inbox"></i> All Submissions
                <span class="sb-badge" x-text="stats.pending" x-show="stats.pending > 0"></span>
            </a>

            <span class="sb-section">Administration</span>
            <a class="nav-item" :class="{active: view==='users'}" @click.prevent="showView('users')" href="#">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a class="nav-item" :class="{active: view==='notices'}" @click.prevent="showView('notices')" href="#">
                <i class="fas fa-bullhorn"></i> Notices
            </a>
            <a class="nav-item" :class="{active: view==='legacy'}" @click.prevent="showView('legacy')" href="#">
                <i class="fas fa-archive"></i> Legacy Records
            </a>
            <a class="nav-item" :class="{active: view==='audit'}" @click.prevent="showView('audit')" href="#">
                <i class="fas fa-history"></i> Audit Trail
            </a>
        </nav>

        <div class="sb-footer">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="avatar">A</div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;font-weight:600;color:#f1f5f9;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Administrator</p>
                    <p style="font-size:11px;color:#64748b;margin:0;">admin role</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:16px;" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ═══ MAIN AREA ══════════════════════════════════════════════════════════ --}}
    <div class="main-area">
        {{-- Topbar --}}
        <header class="topbar">
            <div>
                <h2 style="font-size:17px;font-weight:700;color:#f1f5f9;margin:0;" x-text="viewTitle"></h2>
                <p style="font-size:12px;color:#64748b;margin:0;" x-text="viewSub"></p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                {{-- Notification Bell --}}
                <div style="position:relative;">
                    @php
                        $allNotifications = collect();
                        foreach($announcements as $notice) {
                            $allNotifications->push((object)[
                                'type'    => 'notice',
                                'title'   => $notice->subject,
                                'content' => $notice->content,
                                'priority'=> $notice->priority,
                                'date'    => $notice->posted_at,
                            ]);
                        }
                        $allNotifications = $allNotifications->sortByDesc('date')->take(8);
                    @endphp
                    <button @click="notifOpen = !notifOpen"
                        style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;color:#94a3b8;font-size:15px;">
                        <i class="fas fa-bell"></i>
                        @if($allNotifications->count() > 0)
                        <span style="position:absolute;top:6px;right:6px;width:8px;height:8px;background:#ef4444;border-radius:50%;border:2px solid #1e293b;"></span>
                        @endif
                    </button>
                    <div x-show="notifOpen" @click.outside="notifOpen=false" x-transition
                        style="position:absolute;top:calc(100% + 8px);right:0;width:320px;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:16px;overflow:hidden;z-index:100;box-shadow:0 20px 60px rgba(0,0,0,.5);">
                        <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:14px;font-weight:700;color:#f1f5f9;">Notifications</span>
                            <span style="font-size:11px;background:rgba(99,102,241,.2);color:#a5b4fc;padding:2px 8px;border-radius:20px;font-weight:600;">{{ $allNotifications->count() }}</span>
                        </div>
                        <div style="max-height:300px;overflow-y:auto;">
                            @forelse($allNotifications as $n)
                            <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.04);cursor:pointer;" @click="showView('notices');notifOpen=false">
                                <p style="font-size:13px;font-weight:600;color:#f1f5f9;margin:0 0 4px;">{{ $n->title }}</p>
                                <p style="font-size:12px;color:#64748b;margin:0;">{{ Str::limit($n->content, 60) }}</p>
                                <p style="font-size:11px;color:#475569;margin:4px 0 0;">{{ $n->date?->diffForHumans() }}</p>
                            </div>
                            @empty
                            <div style="padding:24px;text-align:center;color:#475569;font-size:13px;">No notifications</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="avatar" style="font-size:11px;">AD</div>
            </div>
        </header>

        <div class="content-area">

            {{-- ── DASHBOARD VIEW ─────────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='dashboard'}">
                <div class="grid-4" style="margin-bottom:24px;">
                    <div class="stat-card" style="border-left:3px solid #6366f1;cursor:pointer;" @click="showView('templates')">
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
                            <div class="stat-icon" style="background:rgba(99,102,241,.15);color:#818cf8;"><i class="fas fa-file-excel"></i></div>
                        </div>
                        <div class="stat-val" x-text="stats.templates">0</div>
                        <div class="stat-lbl">Total Templates</div>
                    </div>
                    <div class="stat-card" style="border-left:3px solid #10b981;cursor:pointer;" @click="showView('submissions')">
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
                            <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399;"><i class="fas fa-inbox"></i></div>
                        </div>
                        <div class="stat-val" x-text="stats.total_submissions">0</div>
                        <div class="stat-lbl">Total Submissions</div>
                    </div>
                    <div class="stat-card" style="border-left:3px solid #f59e0b;cursor:pointer;" @click="showView('submissions');filterSubmissions('submitted')">
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
                            <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#fbbf24;"><i class="fas fa-clock"></i></div>
                        </div>
                        <div class="stat-val" x-text="stats.pending">0</div>
                        <div class="stat-lbl">Pending Review</div>
                    </div>
                    <div class="stat-card" style="border-left:3px solid #06b6d4;cursor:pointer;" @click="showView('users')">
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
                            <div class="stat-icon" style="background:rgba(6,182,212,.15);color:#22d3ee;"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="stat-val" x-text="stats.users">0</div>
                        <div class="stat-lbl">Registered Users</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
                    {{-- Approval Breakdown --}}
                    <div class="panel">
                        <p class="panel-title">Submission Status Breakdown</p>
                        <p class="panel-sub">Overview of all submitted forms</p>
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <template x-for="s in submissionBreakdown" :key="s.label">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div :class="s.dotClass" style="width:10px;height:10px;border-radius:50%;flex-shrink:0;"></div>
                                    <span style="font-size:13px;color:#94a3b8;flex:1;" x-text="s.label"></span>
                                    <div style="flex:2;background:rgba(255,255,255,.05);border-radius:6px;height:8px;overflow:hidden;">
                                        <div :style="'width:'+s.pct+'%;background:'+s.color+';height:100%;border-radius:6px;transition:width .5s'"></div>
                                    </div>
                                    <span style="font-size:13px;font-weight:600;color:#f1f5f9;width:30px;text-align:right;" x-text="s.count"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    {{-- Recent Submissions --}}
                    <div class="panel">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <div><p class="panel-title" style="margin-bottom:0;">Recent Submissions</p></div>
                            <button class="btn btn-ghost" style="padding:5px 12px;font-size:12px;" @click="showView('submissions')">View All</button>
                        </div>
                        <table class="data-table">
                            <thead><tr><th>User</th><th>Template</th><th>Status</th></tr></thead>
                            <tbody>
                                <template x-if="recentSubmissions.length === 0">
                                    <tr><td colspan="3" style="text-align:center;color:#475569;padding:24px;">No submissions yet</td></tr>
                                </template>
                                <template x-for="s in recentSubmissions.slice(0,6)" :key="s.id">
                                    <tr>
                                        <td x-text="s.user_name ?? '—'"></td>
                                        <td x-text="s.template_name ?? '—'" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></td>
                                        <td><span class="badge" :class="'badge-'+s.status" x-text="s.status_label ?? s.status"></span></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notices preview --}}
                <div class="panel">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <div><p class="panel-title" style="margin-bottom:0;">Latest Announcements</p></div>
                        <button class="btn btn-ghost" style="padding:5px 12px;font-size:12px;" @click="showView('notices')">Manage</button>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
                        @forelse($announcements as $notice)
                        <div style="background:rgba(15,23,42,.6);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:16px;">
                            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                                <p style="font-size:13px;font-weight:600;color:#f1f5f9;margin:0;">{{ $notice->subject }}</p>
                                <span class="badge badge-{{ strtolower($notice->priority) === 'high' ? 'rejected' : (strtolower($notice->priority) === 'medium' ? 'review' : 'draft') }}" style="flex-shrink:0;margin-left:8px;">{{ $notice->priority }}</span>
                            </div>
                            <p style="font-size:12px;color:#64748b;margin:0;">{{ Str::limit($notice->content, 80) }}</p>
                        </div>
                        @empty
                        <p style="color:#475569;font-size:13px;">No announcements posted.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ── TEMPLATES VIEW ─────────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='templates'}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <div></div>
                    <button class="btn btn-primary" @click="openUploadModal()"><i class="fas fa-upload"></i> Upload New Template</button>
                </div>

                <div class="panel" style="margin-bottom:20px;" x-show="templates.length===0 && !loadingTemplates">
                    <div class="empty-state">
                        <i class="fas fa-file-excel"></i>
                        <p>No IPCRF templates uploaded yet.</p>
                        <button class="btn btn-primary" style="margin-top:16px;" @click="openUploadModal()"><i class="fas fa-upload"></i> Upload First Template</button>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;" x-show="templates.length>0">
                    <template x-for="t in templates" :key="t.id">
                        <div class="panel" style="cursor:default;position:relative;">
                            <div style="display:flex;align-items:start;gap:12px;margin-bottom:14px;">
                                <div style="width:44px;height:44px;background:rgba(99,102,241,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-file-excel" style="color:#818cf8;font-size:18px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <p style="font-size:14px;font-weight:700;color:#f1f5f9;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="t.name"></p>
                                    <p style="font-size:12px;color:#64748b;margin:0;" x-text="t.description || 'No description'"></p>
                                </div>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
                                <template x-for="pos in (t.positions || [])" :key="pos">
                                    <span style="font-size:11px;background:rgba(99,102,241,.15);color:#a5b4fc;padding:2px 8px;border-radius:20px;font-weight:500;" x-text="pos"></span>
                                </template>
                                <span x-show="!t.positions || t.positions.length===0" style="font-size:11px;color:#475569;">No positions assigned</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#475569;margin-bottom:14px;">
                                <span><i class="fas fa-th" style="margin-right:4px;"></i> <span x-text="t.field_count"></span> fields</span>
                                <span><i class="fas fa-calendar" style="margin-right:4px;"></i> <span x-text="t.created_at"></span></span>
                            </div>
                            <div style="display:flex;gap:8px;">
                                <a :href="'/admin/templates/'+t.id+'/builder'" class="btn btn-primary" style="flex:1;justify-content:center;font-size:12px;">
                                    <i class="fas fa-magic"></i> Builder
                                </a>
                                <button class="btn btn-danger" @click="deleteTemplate(t.id,t.name)" style="font-size:12px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ── POSITIONS VIEW ─────────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='positions'}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <div></div>
                    <button class="btn btn-primary" @click="openPositionModal()"><i class="fas fa-plus"></i> Add Position</button>
                </div>
                <div class="panel">
                    <div x-show="positions.length===0" class="empty-state">
                        <i class="fas fa-id-badge"></i>
                        <p>No positions added yet.</p>
                    </div>
                    <table class="data-table" x-show="positions.length>0">
                        <thead><tr><th>#</th><th>Position Name</th><th>Description</th><th>Users</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <template x-for="p in positions" :key="p.id">
                                <tr>
                                    <td x-text="p.id"></td>
                                    <td style="font-weight:600;color:#f1f5f9;" x-text="p.name"></td>
                                    <td x-text="p.description || '—'" style="color:#64748b;"></td>
                                    <td><span class="badge badge-submitted" x-text="p.users_count+' users'"></span></td>
                                    <td><span class="badge" :class="p.is_active ? 'badge-approved' : 'badge-rejected'" x-text="p.is_active ? 'Active' : 'Inactive'"></span></td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <button class="btn btn-ghost" style="padding:4px 10px;font-size:11px;" @click="editPosition(p)"><i class="fas fa-pen"></i></button>
                                            <button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" @click="deletePosition(p.id,p.name)"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── SUBMISSIONS VIEW ───────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='submissions'}">
                {{-- Filter Tabs --}}
                <div class="tab-bar" style="max-width:600px;">
                    <button class="tab-btn" :class="{active:submissionFilter===''}" @click="submissionFilter='';loadSubmissions()">All</button>
                    <button class="tab-btn" :class="{active:submissionFilter==='submitted'}" @click="filterSubmissions('submitted')">Pending</button>
                    <button class="tab-btn" :class="{active:submissionFilter==='under_review'}" @click="filterSubmissions('under_review')">Under Review</button>
                    <button class="tab-btn" :class="{active:submissionFilter==='approved'}" @click="filterSubmissions('approved')">Approved</button>
                    <button class="tab-btn" :class="{active:submissionFilter==='rejected'}" @click="filterSubmissions('rejected')">Rejected</button>
                </div>

                <div class="panel">
                    <div style="margin-bottom:16px;" class="search-wrap">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search by user name..." x-model="submissionSearch" @input.debounce.400ms="loadSubmissions()">
                    </div>
                    <div x-show="submissions.length===0" class="empty-state">
                        <i class="fas fa-inbox"></i><p>No submissions found.</p>
                    </div>
                    <table class="data-table" x-show="submissions.length>0">
                        <thead><tr><th>#</th><th>User</th><th>Position</th><th>Template</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <template x-for="s in submissions" :key="s.id">
                                <tr>
                                    <td x-text="s.id"></td>
                                    <td style="font-weight:600;color:#f1f5f9;" x-text="s.user?.name || '—'"></td>
                                    <td x-text="s.user?.position?.name || '—'" style="color:#64748b;font-size:12px;"></td>
                                    <td x-text="s.template?.name || '—'" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></td>
                                    <td x-text="s.submitted_at ? formatDate(s.submitted_at) : '—'" style="font-size:12px;color:#64748b;"></td>
                                    <td><span class="badge" :class="'badge-'+s.status" x-text="statusLabel(s.status)"></span></td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <button class="btn btn-ghost" style="padding:4px 10px;font-size:11px;" @click="viewSubmission(s)"><i class="fas fa-eye"></i></button>
                                            <template x-if="s.status==='submitted' || s.status==='under_review'">
                                                <button class="btn btn-success" style="padding:4px 10px;font-size:11px;" @click="approveSubmission(s.id)"><i class="fas fa-check"></i></button>
                                            </template>
                                            <template x-if="s.status==='submitted' || s.status==='under_review'">
                                                <button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" @click="openRejectModal(s.id)"><i class="fas fa-times"></i></button>
                                            </template>
                                            <template x-if="s.status==='approved'">
                                                <a :href="'/admin/submissions/'+s.id+'/download'" class="btn btn-ghost" style="padding:4px 10px;font-size:11px;"><i class="fas fa-download"></i></a>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── USERS VIEW ─────────────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='users'}">
                <div class="panel">
                    <div style="margin-bottom:16px;" class="search-wrap">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search users by name or email..." x-model="userSearch" @input.debounce.400ms="loadUsers()">
                    </div>
                    <div x-show="users.length===0" class="empty-state">
                        <i class="fas fa-users"></i><p>No users registered yet.</p>
                    </div>
                    <table class="data-table" x-show="users.length>0">
                        <thead><tr><th>Name</th><th>Email</th><th>Position</th><th>Department</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <template x-for="u in users" :key="u.id">
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div class="avatar" style="width:30px;height:30px;font-size:10px;" x-text="(u.name||'U').charAt(0).toUpperCase()"></div>
                                            <span style="color:#f1f5f9;font-weight:600;" x-text="u.name"></span>
                                        </div>
                                    </td>
                                    <td x-text="u.email" style="font-size:12px;color:#64748b;"></td>
                                    <td x-text="u.position?.name || '—'" style="font-size:12px;"></td>
                                    <td x-text="u.department || '—'" style="font-size:12px;color:#64748b;"></td>
                                    <td><span class="badge" :class="u.approved ? 'badge-approved' : 'badge-review'" x-text="u.approved ? 'Approved' : 'Pending'"></span></td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <button class="btn btn-ghost" style="padding:4px 10px;font-size:11px;" @click="viewUser(u)"><i class="fas fa-eye"></i></button>
                                            <button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" @click="deleteUser(u.id,u.name)"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── NOTICES VIEW ───────────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='notices'}">
                <div style="display:grid;grid-template-columns:400px 1fr;gap:24px;">
                    <div class="panel">
                        <p class="panel-title">Post Announcement</p>
                        <p class="panel-sub">Broadcast to all users</p>
                        <form id="noticeForm" method="POST" action="{{ route('admin.notices.store') }}" @submit.prevent="submitNotice($event)">
                            @csrf
                            <div style="margin-bottom:14px;">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-input" placeholder="Announcement title" required>
                            </div>
                            <div style="margin-bottom:14px;">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-input" rows="4" placeholder="Write your announcement..." required style="resize:vertical;"></textarea>
                            </div>
                            <div style="margin-bottom:20px;">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-input">
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-paper-plane"></i> Post Announcement</button>
                        </form>
                    </div>
                    <div class="panel">
                        <p class="panel-title">Posted Announcements</p>
                        <p class="panel-sub">All active notices</p>
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            @forelse($announcements as $notice)
                            <div style="background:rgba(15,23,42,.6);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:start;gap:12px;">
                                <div style="flex:1;">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                        <p style="font-size:13px;font-weight:700;color:#f1f5f9;margin:0;">{{ $notice->subject }}</p>
                                        <span class="badge badge-{{ strtolower($notice->priority)==='high'?'rejected':(strtolower($notice->priority)==='medium'?'review':'draft') }}">{{ $notice->priority }}</span>
                                    </div>
                                    <p style="font-size:12px;color:#64748b;margin:0 0 6px;">{{ $notice->content }}</p>
                                    <p style="font-size:11px;color:#475569;margin:0;">{{ $notice->posted_at?->format('M j, Y g:i A') }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.notices.destroy', $notice->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding:4px 10px;font-size:11px;" onclick="return confirm('Delete this notice?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                            @empty
                            <div class="empty-state"><i class="fas fa-bullhorn"></i><p>No announcements posted yet.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── LEGACY RECORDS VIEW ────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='legacy'}">
                <div class="panel">
                    <p class="panel-title">Legacy IPCRF Records</p>
                    <p class="panel-sub" style="margin-bottom:20px;">Previously uploaded IPCRF files</p>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Employee</th><th>Region</th><th>Date Uploaded</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($recentSubmissions as $record)
                                <tr>
                                    <td style="font-weight:600;color:#f1f5f9;">{{ $record->employee?->fullName() ?? 'N/A' }}</td>
                                    <td style="font-size:12px;color:#64748b;">{{ optional(optional(optional($record->employee)->school)->municipality)->province->name ?? '—' }}</td>
                                    <td style="font-size:12px;color:#64748b;">{{ $record->uploaded_at ? $record->uploaded_at->format('M j, Y') : 'N/A' }}</td>
                                    <td><span class="badge badge-approved">{{ $record->status }}</span></td>
                                    <td>
                                        @if($record->id)
                                        <div style="display:flex;gap:6px;">
                                            <a href="{{ route('admin.records.download', $record->id) }}{{ isset($record->is_wizard) ? '?source=ipcrfs' : '' }}" class="btn btn-ghost" style="padding:4px 10px;font-size:11px;"><i class="fas fa-download"></i></a>
                                            <form method="POST" action="{{ route('admin.records.destroy', $record->id) }}{{ isset($record->is_wizard) ? '?source=ipcrfs' : '' }}" style="margin:0;" onsubmit="return confirm('Delete this record?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="padding:4px 10px;font-size:11px;"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" style="text-align:center;color:#475569;padding:32px;">No legacy records</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── AUDIT TRAIL VIEW ───────────────────────────────────────── --}}
            <div class="view-section" :class="{active: view==='audit'}">
                <div class="panel">
                    <p class="panel-title">Audit Trail</p>
                    <p class="panel-sub" style="margin-bottom:20px;">System activity log</p>
                    <div x-show="auditLogs.length===0" class="empty-state"><i class="fas fa-history"></i><p>No audit logs yet.</p></div>
                    <table class="data-table" x-show="auditLogs.length>0">
                        <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
                        <tbody>
                            <template x-for="log in auditLogs" :key="log.id">
                                <tr>
                                    <td style="font-size:11px;color:#64748b;" x-text="formatDate(log.created_at)"></td>
                                    <td x-text="log.user?.name || 'System'"></td>
                                    <td><code style="font-size:11px;background:rgba(99,102,241,.15);color:#a5b4fc;padding:2px 6px;border-radius:4px;" x-text="log.action"></code></td>
                                    <td style="font-size:12px;" x-text="log.entity_type ? log.entity_type+' #'+log.entity_id : '—'"></td>
                                    <td style="font-size:11px;color:#64748b;" x-text="log.details ? JSON.stringify(log.details).substring(0,60) : '—'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /content-area --}}
    </div>{{-- /main-area --}}

    {{-- ═══ MODALS ══════════════════════════════════════════════════════════ --}}

    {{-- Upload Template Modal --}}
    <div class="modal-overlay" x-show="uploadModal" x-transition @click.self="uploadModal=false" style="display:none;">
        <div class="modal-box">
            <p class="modal-title"><i class="fas fa-upload" style="color:#6366f1;margin-right:8px;"></i>Upload IPCRF Template</p>
            <form @submit.prevent="submitTemplateUpload($event)">
                @csrf
                <div style="margin-bottom:16px;">
                    <label class="form-label">Template Name *</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g., IPCRF Form 2025 — Officer" required>
                </div>
                <div style="margin-bottom:16px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="2" placeholder="Optional description..."></textarea>
                </div>
                <div style="margin-bottom:20px;">
                    <label class="form-label">Excel File (.xlsx) *</label>
                    <div class="upload-zone" id="template-drop-zone" @dragover.prevent="$el.classList.add('dragover')" @dragleave="$el.classList.remove('dragover')" @drop.prevent="handleTemplateDrop($event)" @click="$refs.templateFile.click()">
                        <i class="fas fa-file-excel" style="font-size:36px;color:#6366f1;margin-bottom:12px;display:block;"></i>
                        <p style="font-size:14px;font-weight:600;color:#f1f5f9;margin:0 0 4px;">Drop XLSX file here or click to browse</p>
                        <p style="font-size:12px;color:#64748b;margin:0;" x-text="uploadFileName || 'Only .xlsx files accepted'"></p>
                        <input type="file" name="file" x-ref="templateFile" accept=".xlsx" style="display:none;" @change="uploadFileName=$event.target.files[0]?.name" required>
                    </div>
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="button" class="btn btn-ghost" style="flex:1;justify-content:center;" @click="uploadModal=false">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center;" :disabled="uploading">
                        <span x-show="!uploading"><i class="fas fa-upload"></i> Upload & Parse</span>
                        <span x-show="uploading"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add/Edit Position Modal --}}
    <div class="modal-overlay" x-show="positionModal" x-transition @click.self="positionModal=false" style="display:none;">
        <div class="modal-box" style="min-width:400px;">
            <p class="modal-title" x-text="editingPosition ? 'Edit Position' : 'Add New Position'"></p>
            <form @submit.prevent="submitPosition($event)">
                <div style="margin-bottom:16px;">
                    <label class="form-label">Position Name *</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g., Social Welfare Officer III" x-model="positionForm.name" required>
                </div>
                <div style="margin-bottom:20px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="2" placeholder="Optional..." x-model="positionForm.description"></textarea>
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="button" class="btn btn-ghost" style="flex:1;justify-content:center;" @click="positionModal=false">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center;"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- View Submission Modal --}}
    <div class="modal-overlay" x-show="submissionModal" x-transition @click.self="submissionModal=false" style="display:none;">
        <div class="modal-box" style="min-width:560px;">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px;">
                <p class="modal-title" style="margin:0;">Submission Details</p>
                <button @click="submissionModal=false" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:18px;"><i class="fas fa-times"></i></button>
            </div>
            <template x-if="selectedSubmission">
                <div>
                    <div style="background:rgba(15,23,42,.6);border-radius:12px;padding:16px;margin-bottom:16px;">
                        <div class="grid-2">
                            <div>
                                <p style="font-size:11px;color:#475569;margin:0 0 4px;">USER</p>
                                <p style="font-size:14px;font-weight:600;color:#f1f5f9;margin:0;" x-text="selectedSubmission.user?.name || '—'"></p>
                            </div>
                            <div>
                                <p style="font-size:11px;color:#475569;margin:0 0 4px;">POSITION</p>
                                <p style="font-size:14px;color:#94a3b8;margin:0;" x-text="selectedSubmission.user?.position?.name || '—'"></p>
                            </div>
                            <div>
                                <p style="font-size:11px;color:#475569;margin:0 0 4px;">TEMPLATE</p>
                                <p style="font-size:14px;color:#94a3b8;margin:0;" x-text="selectedSubmission.template?.name || '—'"></p>
                            </div>
                            <div>
                                <p style="font-size:11px;color:#475569;margin:0 0 4px;">STATUS</p>
                                <span class="badge" :class="'badge-'+selectedSubmission.status" x-text="statusLabel(selectedSubmission.status)"></span>
                            </div>
                        </div>
                    </div>
                    <div x-show="selectedSubmission.admin_remarks" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:12px;margin-bottom:16px;">
                        <p style="font-size:12px;color:#fbbf24;margin:0 0 4px;font-weight:600;">Admin Remarks</p>
                        <p style="font-size:13px;color:#94a3b8;margin:0;" x-text="selectedSubmission.admin_remarks"></p>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <template x-if="selectedSubmission.status==='submitted'||selectedSubmission.status==='under_review'">
                            <button class="btn btn-success" @click="approveSubmission(selectedSubmission.id);submissionModal=false">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </template>
                        <template x-if="selectedSubmission.status==='submitted'||selectedSubmission.status==='under_review'">
                            <button class="btn btn-danger" @click="submissionModal=false;openRejectModal(selectedSubmission.id)">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </template>
                        <template x-if="selectedSubmission.status==='approved'">
                            <a :href="'/admin/submissions/'+selectedSubmission.id+'/download'" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download XLSX
                            </a>
                        </template>
                        <button class="btn btn-ghost" @click="submissionModal=false">Close</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal-overlay" x-show="rejectModal" x-transition @click.self="rejectModal=false" style="display:none;">
        <div class="modal-box" style="min-width:420px;">
            <p class="modal-title"><i class="fas fa-times-circle" style="color:#ef4444;margin-right:8px;"></i>Reject Submission</p>
            <div style="margin-bottom:16px;">
                <label class="form-label">Remarks / Reason for Rejection *</label>
                <textarea class="form-input" rows="4" x-model="rejectRemarks" placeholder="Explain why this submission is being rejected..."></textarea>
            </div>
            <div style="display:flex;gap:12px;">
                <button class="btn btn-ghost" style="flex:1;justify-content:center;" @click="rejectModal=false">Cancel</button>
                <button class="btn btn-danger" style="flex:2;justify-content:center;" @click="confirmReject()"><i class="fas fa-times"></i> Confirm Reject</button>
            </div>
        </div>
    </div>

    {{-- User Details Modal --}}
    <div class="modal-overlay" x-show="userModal" x-transition @click.self="userModal=false" style="display:none;">
        <div class="modal-box" style="min-width:480px;">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px;">
                <p class="modal-title" style="margin:0;">User Details</p>
                <button @click="userModal=false" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:18px;"><i class="fas fa-times"></i></button>
            </div>
            <template x-if="selectedUser">
                <div>
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
                        <div class="avatar" style="width:52px;height:52px;font-size:20px;" x-text="(selectedUser.name||'U').charAt(0).toUpperCase()"></div>
                        <div>
                            <p style="font-size:16px;font-weight:700;color:#f1f5f9;margin:0;" x-text="selectedUser.name"></p>
                            <p style="font-size:12px;color:#64748b;margin:2px 0 0;" x-text="selectedUser.email"></p>
                        </div>
                    </div>
                    <div class="grid-2" style="margin-bottom:16px;">
                        <div>
                            <p style="font-size:11px;color:#475569;margin:0 0 4px;">EMPLOYEE ID</p>
                            <p style="font-size:13px;color:#94a3b8;margin:0;" x-text="selectedUser.employee_id || '—'"></p>
                        </div>
                        <div>
                            <p style="font-size:11px;color:#475569;margin:0 0 4px;">POSITION</p>
                            <p style="font-size:13px;color:#94a3b8;margin:0;" x-text="selectedUser.position?.name || '—'"></p>
                        </div>
                        <div>
                            <p style="font-size:11px;color:#475569;margin:0 0 4px;">DEPARTMENT</p>
                            <p style="font-size:13px;color:#94a3b8;margin:0;" x-text="selectedUser.department || '—'"></p>
                        </div>
                        <div>
                            <p style="font-size:11px;color:#475569;margin:0 0 4px;">OFFICE</p>
                            <p style="font-size:13px;color:#94a3b8;margin:0;" x-text="selectedUser.office || '—'"></p>
                        </div>
                    </div>
                    <button class="btn btn-ghost" @click="userModal=false" style="width:100%;justify-content:center;">Close</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Toast --}}
    <div class="toast" :class="toast.type ? 'toast-'+toast.type : ''" x-ref="toast" id="toast">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" :style="'color:'+(toast.type==='success'?'#10b981':'#ef4444')"></i>
        <span x-text="toast.message"></span>
    </div>

</div>{{-- /layout --}}
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function adminApp() {
    return {
        view: 'dashboard',
        viewTitle: 'Dashboard Overview',
        viewSub: 'IPCRF Management System',
        notifOpen: false,

        // Stats
        stats: { templates: 0, total_submissions: 0, pending: 0, users: 0, approved: 0, rejected: 0 },
        submissionBreakdown: [],
        recentSubmissions: [],

        // Templates
        templates: [], loadingTemplates: false,
        uploadModal: false, uploading: false, uploadFileName: '',

        // Positions
        positions: [], positionModal: false, editingPosition: null,
        positionForm: { name: '', description: '' },

        // Submissions
        submissions: [], submissionFilter: '', submissionSearch: '',
        submissionModal: false, selectedSubmission: null,
        rejectModal: false, rejectRemarks: '', rejectTargetId: null,

        // Users
        users: [], userModal: false, selectedUser: null, userSearch: '',

        // Audit
        auditLogs: [],

        // Toast
        toast: { message: '', type: '' },

        viewMeta: {
            dashboard:    { title: 'Dashboard Overview',       sub: 'IPCRF Management System' },
            templates:    { title: 'IPCRF Templates',          sub: 'Upload and configure IPCRF form templates' },
            positions:    { title: 'Employee Positions',       sub: 'Manage position-based template access' },
            submissions:  { title: 'Submissions',              sub: 'Review, approve and reject IPCRF submissions' },
            users:        { title: 'User Management',          sub: 'Manage registered user accounts' },
            notices:      { title: 'Announcements & Notices',  sub: 'Post and manage system announcements' },
            legacy:       { title: 'Legacy IPCRF Records',     sub: 'Previously uploaded IPCRF files' },
            audit:        { title: 'Audit Trail',              sub: 'System activity log' },
        },

        async init() {
            await this.loadStats();
            this.loadTemplates();
            this.loadPositions();
        },

        showView(v) {
            this.view = v;
            const meta = this.viewMeta[v] || { title: v, sub: '' };
            this.viewTitle = meta.title;
            this.viewSub   = meta.sub;
            if (v === 'submissions')  this.loadSubmissions();
            if (v === 'users')        this.loadUsers();
            if (v === 'audit')        this.loadAuditLogs();
        },

        async loadStats() {
            try {
                const r = await fetch('/admin/submissions?stats=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                // submissions endpoint with pagination
                const pending  = d.submissions?.filter(s => ['submitted','under_review'].includes(s.status)).length ?? 0;
                const approved = d.submissions?.filter(s => s.status === 'approved').length ?? 0;
                const rejected = d.submissions?.filter(s => s.status === 'rejected').length ?? 0;
                const total    = d.pagination?.total ?? d.submissions?.length ?? 0;
                this.stats.total_submissions = total;
                this.stats.pending  = d.pagination ? (await this.fetchSubmissionCount('submitted')) : pending;
                this.stats.approved = approved;
                this.stats.rejected = rejected;
                this.recentSubmissions = (d.submissions || []).slice(0, 6).map(s => ({
                    id: s.id, status: s.status,
                    status_label: this.statusLabel(s.status),
                    user_name: s.user?.name,
                    template_name: s.template?.name,
                }));
                this.buildBreakdown();
            } catch(e) {}

            try {
                const rt = await fetch('/admin/templates/all', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const dt = await rt.json();
                this.stats.templates = (dt.templates || []).length;
            } catch(e) {}

            try {
                const ru = await fetch('/admin/users', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const du = await ru.json();
                this.stats.users = du.pagination?.total ?? (du.users || []).length;
            } catch(e) {}
        },

        async fetchSubmissionCount(status) {
            try {
                const r = await fetch('/admin/submissions?status=' + status, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                return d.pagination?.total ?? (d.submissions || []).length;
            } catch(e) { return 0; }
        },

        buildBreakdown() {
            const total = this.stats.total_submissions || 1;
            this.submissionBreakdown = [
                { label:'Approved',    count: this.stats.approved, color:'#10b981', dotClass:'',  pct: Math.round(this.stats.approved/total*100)   },
                { label:'Pending',     count: this.stats.pending,  color:'#f59e0b', dotClass:'',  pct: Math.round(this.stats.pending/total*100)    },
                { label:'Rejected',    count: this.stats.rejected, color:'#ef4444', dotClass:'',  pct: Math.round(this.stats.rejected/total*100)   },
            ].map(s => ({ ...s, dotClass: 'dot-'+s.label.toLowerCase() }));
        },

        async loadTemplates() {
            this.loadingTemplates = true;
            try {
                const r = await fetch('/admin/templates/all', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.templates = d.templates || [];
            } catch(e) {}
            this.loadingTemplates = false;
        },

        async loadPositions() {
            try {
                const r = await fetch('/admin/positions', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.positions = d.positions || [];
            } catch(e) {}
        },

        async loadSubmissions() {
            try {
                let url = '/admin/submissions?';
                if (this.submissionFilter) url += 'status=' + this.submissionFilter + '&';
                if (this.submissionSearch) url += 'search=' + encodeURIComponent(this.submissionSearch) + '&';
                const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.submissions = d.submissions || [];
            } catch(e) {}
        },

        filterSubmissions(status) {
            this.submissionFilter = status;
            this.loadSubmissions();
        },

        async loadUsers() {
            try {
                let url = '/admin/users?';
                if (this.userSearch) url += 'search=' + encodeURIComponent(this.userSearch) + '&';
                const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.users = d.users || [];
            } catch(e) {}
        },

        async loadAuditLogs() {
            try {
                // Inline fetch from audit_logs — no dedicated endpoint yet, show empty for now
                this.auditLogs = [];
            } catch(e) {}
        },

        openUploadModal() { this.uploadModal = true; this.uploadFileName = ''; },
        handleTemplateDrop(e) {
            const f = e.dataTransfer.files[0];
            if (f && f.name.endsWith('.xlsx')) {
                this.$refs.templateFile.files = e.dataTransfer.files;
                this.uploadFileName = f.name;
            }
        },

        async submitTemplateUpload(e) {
            this.uploading = true;
            const fd = new FormData(e.target);
            try {
                const r = await fetch('/admin/templates/upload', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const d = await r.json();
                if (d.success) {
                    this.showToast('Template uploaded! Opening builder...', 'success');
                    this.uploadModal = false;
                    setTimeout(() => { window.location.href = d.builder_url; }, 1200);
                } else {
                    this.showToast(d.message || 'Upload failed', 'error');
                }
            } catch(err) { this.showToast('Upload error', 'error'); }
            this.uploading = false;
        },

        async deleteTemplate(id, name) {
            if (!confirm('Delete template "' + name + '"? This cannot be undone.')) return;
            const r = await fetch('/admin/templates/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            });
            const d = await r.json();
            if (d.success) { this.showToast('Template deleted.', 'success'); this.loadTemplates(); }
        },

        openPositionModal() { this.editingPosition = null; this.positionForm = { name: '', description: '' }; this.positionModal = true; },
        editPosition(p)     { this.editingPosition = p; this.positionForm = { name: p.name, description: p.description || '' }; this.positionModal = true; },

        async submitPosition(e) {
            const url    = this.editingPosition ? '/admin/positions/' + this.editingPosition.id : '/admin/positions';
            const method = this.editingPosition ? 'PUT' : 'POST';
            try {
                const r = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.positionForm),
                });
                const d = await r.json();
                if (d.success) { this.showToast('Position saved!', 'success'); this.positionModal = false; this.loadPositions(); }
                else           { this.showToast(d.message || 'Error', 'error'); }
            } catch(err) { this.showToast('Error saving position', 'error'); }
        },

        async deletePosition(id, name) {
            if (!confirm('Delete position "' + name + '"?')) return;
            const r = await fetch('/admin/positions/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            });
            const d = await r.json();
            if (d.success) { this.showToast('Position deleted.', 'success'); this.loadPositions(); }
        },

        viewSubmission(s) {
            this.selectedSubmission = s;
            // fetch full details
            fetch('/admin/submissions/' + s.id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(d => { this.selectedSubmission = d.submission || s; this.submissionModal = true; });
        },

        async approveSubmission(id) {
            const r = await fetch('/admin/submissions/' + id + '/approve', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                body: JSON.stringify({ remarks: '' }),
            });
            const d = await r.json();
            if (d.success) { this.showToast('Submission approved!', 'success'); this.loadSubmissions(); await this.loadStats(); }
        },

        openRejectModal(id) { this.rejectTargetId = id; this.rejectRemarks = ''; this.rejectModal = true; },
        async confirmReject() {
            if (!this.rejectRemarks.trim()) { this.showToast('Please enter remarks.', 'error'); return; }
            const r = await fetch('/admin/submissions/' + this.rejectTargetId + '/reject', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                body: JSON.stringify({ remarks: this.rejectRemarks }),
            });
            const d = await r.json();
            if (d.success) { this.showToast('Submission rejected.', 'error'); this.rejectModal = false; this.loadSubmissions(); await this.loadStats(); }
        },

        async viewUser(u) {
            const r = await fetch('/admin/users/' + u.id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const d = await r.json();
            this.selectedUser = d.user || u;
            this.userModal = true;
        },

        async deleteUser(id, name) {
            if (!confirm('Delete user "' + name + '"?')) return;
            const r = await fetch('/admin/users/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            });
            const d = await r.json();
            if (d.success) { this.showToast('User deleted.', 'success'); this.loadUsers(); }
        },

        async submitNotice(e) {
            const fd = new FormData(e.target);
            const r  = await fetch('/admin/notices', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            });
            if (r.ok) { this.showToast('Announcement posted!', 'success'); setTimeout(() => location.reload(), 1000); }
        },

        statusLabel(s) {
            return { draft:'Draft', submitted:'Submitted', under_review:'Under Review', approved:'Approved', rejected:'Rejected' }[s] ?? s;
        },

        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
        },

        showToast(msg, type = 'success') {
            this.toast = { message: msg, type };
            const el = document.getElementById('toast');
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 3000);
        },
    };
}
</script>
@endpush
