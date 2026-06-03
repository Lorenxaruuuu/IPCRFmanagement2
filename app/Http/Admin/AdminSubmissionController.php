<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpcrfSubmission;
use App\Services\XlsxGeneratorService;
use App\Services\TemplateParserService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSubmissionController extends Controller
{
    public function __construct(
        private XlsxGeneratorService $xlsxGenerator,
        private TemplateParserService $parser
    ) {}

    public function index(Request $request)
    {
        $query = IpcrfSubmission::with(['user.jobPosition', 'template', 'reviewer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('firstname', 'like', '%' . $request->search . '%')
                ->orWhere('lastname', 'like', '%' . $request->search . '%')
            );
        }

        $submissions = $query->paginate(20);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'submissions' => $submissions->items(),
                'pagination'  => [
                    'total'        => $submissions->total(),
                    'current_page' => $submissions->currentPage(),
                    'last_page'    => $submissions->lastPage(),
                ],
            ]);
        }

        return response()->json(['submissions' => $submissions]);
    }

    public function show(Request $request, int $id)
    {
        $submission = IpcrfSubmission::with([
            'user.jobPosition',
            'template.fields',
            'answers.field',
            'reviewer',
        ])->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['submission' => $submission]);
        }

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
        if ($template->sheet_data) {
            $parsed['rows'] = $template->sheet_data;
        }
        $htmlTable = $this->parser->toHtmlTable($parsed, $fields, false, true);

        $stats = $this->getStats();
        $totalTemplates = \App\Models\IpcrfTemplate::count();

        return view('admin.submissions.show', [
            'submission' => $submission,
            'htmlTable'  => $htmlTable,
            'stats'      => [
                'pending_reviews' => $stats['pending'],
                'approved'        => $stats['approved'],
                'total_templates' => $totalTemplates,
            ],
        ]);
    }

    public function saveAnswers(Request $request, int $id)
    {
        $sessionUser = session('user');
        abort_unless($sessionUser && ($sessionUser['role'] ?? '') === 'admin', 403);

        $submission = IpcrfSubmission::findOrFail($id);

        $answers = $request->input('answers', []);
        foreach ($answers as $fieldId => $value) {
            \App\Models\SubmissionAnswer::updateOrCreate(
                ['submission_id' => $submission->id, 'template_field_id' => (int)$fieldId],
                ['value' => $value]
            );
        }

        if ($submission->generated_file_path && Storage::disk('private')->exists($submission->generated_file_path)) {
            Storage::disk('private')->delete($submission->generated_file_path);
        }

        $submission->update([
            'generated_file_path' => null
        ]);

        return response()->json(['success' => true, 'message' => 'Reviewer inputs saved.']);
    }

    public function approve(Request $request, int $id)
    {
        $submission = IpcrfSubmission::findOrFail($id);
        $submission->update([
            'status'      => 'rpmo_approved', // Transition to RPMO Approved (pending POO review)
            'admin_remarks' => $request->remarks,
            'reviewed_at' => now(),
            'reviewed_by' => session('user')['id'] ?? null,
        ]);

        AuditService::log('submission_approved_rpmo', null, 'IpcrfSubmission', $id, [
            'remarks' => $request->remarks,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Submission reviewed and approved by RPMO (transferred to POO)!']);
        }

        return redirect()->back()->with('success', 'Submission reviewed and approved by RPMO (transferred to POO)!');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['remarks' => 'required|string']);
        $submission = IpcrfSubmission::findOrFail($id);
        $submission->update([
            'status'      => IpcrfSubmission::STATUS_REJECTED,
            'admin_remarks' => $request->remarks,
            'reviewed_at' => now(),
            'reviewed_by' => session('user')['id'] ?? null,
        ]);

        AuditService::log('submission_rejected', null, 'IpcrfSubmission', $id, [
            'remarks' => $request->remarks,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Submission rejected.']);
        }

        return redirect()->back()->with('success', 'Submission rejected.');
    }

    public function download(int $id)
    {
        $submission = IpcrfSubmission::with(['user.position', 'template', 'answers.field'])->findOrFail($id);
        AuditService::log('submission_downloaded', null, 'IpcrfSubmission', $id);
        return $this->xlsxGenerator->download($submission);
    }

    public function getStats(): array
    {
        return [
            'total'        => IpcrfSubmission::count(),
            'pending'      => IpcrfSubmission::whereIn('status', ['submitted', 'under_review'])->count(),
            'approved'     => IpcrfSubmission::where('status', 'approved')->count(),
            'rejected'     => IpcrfSubmission::where('status', 'rejected')->count(),
            'drafts'       => IpcrfSubmission::where('status', 'draft')->count(),
        ];
    }
}
