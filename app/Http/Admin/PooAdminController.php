<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpcrfSubmission;
use App\Models\Province;
use App\Models\User;
use App\Services\TemplateParserService;
use App\Services\XlsxGeneratorService;
use App\Services\AuditService;
use App\Support\AdminPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PooAdminController extends Controller
{
    public function __construct(
        private TemplateParserService $parser,
        private XlsxGeneratorService $xlsxGenerator,
    ) {}

    public function dashboard()
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);
        $position = AdminPosition::resolve($admin);

        $staffQuery = User::query()
            ->whereIn('role', ['staff', 'encoder', 'viewer'])
            ->where('approved', true);
        AdminPosition::scopeUsersInProvince($staffQuery, $province);

        $submissionQuery = IpcrfSubmission::query()->whereHas('user', function ($q) use ($province) {
            AdminPosition::scopeUsersInProvince($q, $province);
        });

        $stats = [
            'pending_queue'  => (clone $submissionQuery)->whereIn('status', ['submitted', 'under_review'])->count(),
            'approved'       => (clone $submissionQuery)->where('status', 'approved')->count(),
            'provincial_staff' => (clone $staffQuery)->count(),
            'archived'       => (clone $submissionQuery)->where('status', 'approved')->count(),
        ];

        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return view('admin.dashboard-poo', [
            'currentUser'        => $admin,
            'userPosition'       => $position,
            'assignedProvince'   => $province,
            'stats'              => $stats,
            'provinces'          => $provinces,
        ]);
    }

    public function provincialQueue(Request $request)
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);

        if (!$province) {
            return response()->json(['submissions' => [], 'pending_count' => 0, 'province' => null, 'warning' => 'No province assigned.']);
        }

        $query = IpcrfSubmission::with(['user.jobPosition', 'template'])
            ->whereHas('user', fn ($q) => AdminPosition::scopeUsersInProvince($q, $province))
            ->latest('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['submitted', 'under_review']);
        }
        if ($request->filled('date')) {
            $query->whereDate('submitted_at', $request->date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $submissions = $query->limit(100)->get();

        $pendingCount = 0;
        if ($province) {
            $pendingCount = IpcrfSubmission::whereIn('status', ['submitted', 'under_review'])
                ->whereHas('user', fn ($q) => AdminPosition::scopeUsersInProvince($q, $province))
                ->count();
        }

        return response()->json([
            'submissions' => $submissions->map(fn ($s) => $this->formatSubmissionRow($s)),
            'pending_count' => $pendingCount,
            'province' => $province,
            'warning' => $province ? null : 'No province assigned to your account. Contact superadmin.',
        ]);
    }

    public function staffDirectory(Request $request)
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);

        $query = User::with('jobPosition')
            ->whereIn('role', ['staff', 'encoder', 'viewer'])
            ->where('approved', true);

        if ($province) {
            AdminPosition::scopeUsersInProvince($query, $province);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        $staff = $query->orderBy('lastname')->limit(200)->get();

        return response()->json([
            'staff' => $staff->map(fn ($u) => [
                'id'          => $u->id,
                'name'        => $u->full_name,
                'employee_id' => $u->employee_id,
                'email'       => $u->email,
                'position'    => $u->jobPosition?->name ?? '—',
                'department'  => $u->department ?? '—',
                'office'      => $u->office ?? '—',
                'province'    => $u->assigned_province ?? $u->office ?? '—',
            ]),
            'province' => $province,
        ]);
    }

    public function archives(Request $request)
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);

        if (!$province) {
            return response()->json(['archives' => [], 'province' => null]);
        }

        $query = IpcrfSubmission::with(['user.jobPosition', 'template'])
            ->where('status', 'approved')
            ->whereHas('user', fn ($q) => AdminPosition::scopeUsersInProvince($q, $province))
            ->latest('reviewed_at');

        if ($request->filled('year')) {
            $query->whereYear('reviewed_at', $request->year);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $records = $query->limit(100)->get();

        return response()->json([
            'archives' => $records->map(fn ($s) => array_merge($this->formatSubmissionRow($s), [
                'approved_at' => $s->reviewed_at?->format('M j, Y'),
                'download_url' => route('admin.poo.submissions.download', $s->id),
            ])),
            'province' => $province,
        ]);
    }

    public function inspectSubmission(int $id)
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);

        $submission = IpcrfSubmission::with([
            'user.jobPosition',
            'template.fields',
            'answers',
            'reviewer',
        ])->findOrFail($id);

        $this->assertSubmissionInProvince($submission, $province);

        $template = $submission->template;
        $userRecord = $submission->user;

        $answers = $submission->answers->keyBy('template_field_id');
        $position = $userRecord->jobPosition;

        $autofillValues = [
            'autofill_name'       => $userRecord->full_name,
            'autofill_position'   => $position?->name ?? '',
            'autofill_department' => $userRecord->department ?? $userRecord->office ?? '',
            'autofill_date'       => optional($submission->submitted_at)->format('F d, Y') ?? now()->format('F d, Y'),
        ];

        $fields = $template->fields->map(function ($f) use ($answers, $autofillValues) {
            $value = $f->isAutofill()
                ? ($autofillValues[$f->field_type] ?? '')
                : ($answers[$f->id]?->value ?? '');

            return [
                'id'            => $f->id,
                'cell_ref'      => $f->cell_ref,
                'field_type'    => $f->field_type,
                'field_label'   => $f->field_label,
                'current_value' => $value,
            ];
        })->toArray();

        $fullPath = Storage::disk('private')->path($template->file_path);
        $parsed = $this->parser->parse($fullPath);
        $htmlTable = $this->parser->toHtmlTable($parsed, $fields, false, true, true);

        return response()->json([
            'submission' => array_merge($this->formatSubmissionRow($submission), [
                'admin_remarks' => $submission->admin_remarks,
                'feedback'      => $submission->admin_remarks,
            ]),
            'html_table' => $htmlTable,
            'template'   => ['id' => $template->id, 'name' => $template->name],
        ]);
    }

    public function returnForCorrection(Request $request, int $id)
    {
        $request->validate(['remarks' => 'required|string|max:5000']);

        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);
        $submission = IpcrfSubmission::with('user')->findOrFail($id);
        $this->assertSubmissionInProvince($submission, $province);

        if (!in_array($submission->status, ['submitted', 'under_review', 'rejected'], true)) {
            return response()->json(['success' => false, 'message' => 'Only active forms can be returned for correction.'], 422);
        }

        $submission->update([
            'status'        => IpcrfSubmission::STATUS_DRAFT,
            'admin_remarks' => $request->remarks,
            'submitted_at'  => null,
            'reviewed_at'   => now(),
            'reviewed_by'   => $admin->id,
        ]);

        AuditService::log('submission_returned_for_correction', $admin->id, 'IpcrfSubmission', $id, [
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form returned to staff as draft. Your feedback has been saved.',
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);
        $submission = IpcrfSubmission::with('user')->findOrFail($id);
        $this->assertSubmissionInProvince($submission, $province);

        if (!in_array($submission->status, ['submitted', 'under_review'], true)) {
            return response()->json(['success' => false, 'message' => 'This submission cannot be approved in its current state.'], 422);
        }

        $submission->update([
            'status'        => IpcrfSubmission::STATUS_POO_APPROVED,
            'admin_remarks' => $request->input('remarks'),
            'reviewed_at'   => now(),
            'reviewed_by'   => $admin->id,
        ]);

        AuditService::log('submission_approved_poo', $admin->id, 'IpcrfSubmission', $id, [
            'remarks' => $request->input('remarks'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submission approved by POO (transferred to RPMO)!',
        ]);
    }

    public function download(int $id)
    {
        $admin = $this->currentAdmin();
        $province = AdminPosition::assignedProvince($admin);
        $submission = IpcrfSubmission::with(['user', 'template', 'answers.field'])->findOrFail($id);
        $this->assertSubmissionInProvince($submission, $province);

        if ($submission->status !== IpcrfSubmission::STATUS_APPROVED) {
            abort(403, 'Only approved submissions can be downloaded.');
        }

        AuditService::log('submission_downloaded', $admin->id, 'IpcrfSubmission', $id);

        return $this->xlsxGenerator->download($submission);
    }

    private function currentAdmin(): User
    {
        $sessionUser = session('user');
        abort_unless($sessionUser, 403);

        return User::findOrFail($sessionUser['id'] ?? 0);
    }

    private function assertSubmissionInProvince(IpcrfSubmission $submission, ?string $province): void
    {
        if (!$province) {
            return;
        }

        $user = $submission->user;
        $inProvince = ($user->assigned_province === $province)
            || (is_string($user->office) && str_contains($user->office, $province));

        abort_unless($inProvince, 403, 'This submission is outside your designated province.');
    }

    private function formatSubmissionRow(IpcrfSubmission $s): array
    {
        return [
            'id'           => $s->id,
            'employee'     => $s->user?->full_name ?? 'Unknown',
            'employee_id'  => $s->user?->employee_id,
            'position'     => $s->user?->jobPosition?->name ?? '—',
            'department'   => $s->user?->department ?? $s->user?->office ?? '—',
            'template'     => $s->template?->name ?? '—',
            'status'       => $s->status,
            'status_label' => $s->statusLabel(),
            'submitted_at' => $s->submitted_at?->format('M j, Y g:i A') ?? '—',
            'semester'     => $s->template?->semester ?? '—',
        ];
    }
}
