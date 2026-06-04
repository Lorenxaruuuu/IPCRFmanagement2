<?php

namespace App\Http\Controllers;

use App\Models\IpcrfTemplate;
use App\Models\IpcrfSubmission;
use App\Models\SubmissionAnswer;
use App\Models\TemplateField;
use App\Services\TemplateParserService;
use App\Services\XlsxGeneratorService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    public function __construct(
        private TemplateParserService $parser,
        private XlsxGeneratorService  $xlsxGenerator
    ) {}

    /**
     * Get data for user dashboard (assigned templates, drafts, submissions).
     */
    public function index()
    {
        $sessionUser = session('user');
        $user        = DB::table('users')->where('id', $sessionUser['id'] ?? 0)->first();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Templates assigned to user's position
        $assignedTemplates = IpcrfTemplate::select('id', 'name', 'description', 'is_active')
            ->active()
            ->whereHas('positions', fn($q) => $q->where('positions.id', $user->position_id))
            ->with('positions')
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'description' => $t->description,
                'field_count' => $t->fields()->count(),
                'positions'   => $t->positions->pluck('name'),
            ]);

        // User's submissions
        $submissions = IpcrfSubmission::with('template')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'template_id'  => $s->template_id,
                'template_name' => $s->template?->name ?? 'N/A',
                'status'       => $s->status,
                'status_label' => $s->statusLabel(),
                'submitted_at' => $s->submitted_at?->format('M d, Y'),
                'updated_at'   => $s->updated_at->format('M d, Y'),
            ]);

        return response()->json([
            'assigned_templates' => $assignedTemplates,
            'submissions'        => $submissions,
            'stats' => [
                'assigned'  => $assignedTemplates->count(),
                'drafts'    => $submissions->where('status', 'draft')->count(),
                'submitted' => $submissions->whereIn('status', ['submitted', 'poo_approved', 'under_review'])->count(),
                'approved'  => $submissions->where('status', 'approved')->count(),
            ],
        ]);
    }

    /**
     * Render a template as an interactive form for the user to fill.
     */
    public function fillForm(Request $request, int $templateId)
    {
        $sessionUser = session('user');
        $userRecord  = DB::table('users')->where('id', $sessionUser['id'] ?? 0)->first();

        $template = IpcrfTemplate::with(['fields', 'positions'])->findOrFail($templateId);

        // Check access: user's position must be assigned to this template
        $hasAccess = $template->positions()
            ->where('positions.id', $userRecord->position_id ?? 0)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        // Find or create a draft submission
        $submission = IpcrfSubmission::firstOrCreate(
            ['template_id' => $templateId, 'user_id' => $userRecord->id, 'status' => 'draft'],
            ['status' => 'draft']
        );

        // Load existing answers
        $answers = SubmissionAnswer::where('submission_id', $submission->id)
            ->get()
            ->keyBy('template_field_id');

        // Parse the template
        $fullPath = Storage::disk('private')->path($template->file_path);
        $parsed   = $this->parser->parse($fullPath);
        if ($template->sheet_data) {
            $parsed['rows'] = $template->sheet_data;
        }

        // Resolve autofill values from user profile
        $position = DB::table('positions')->find($userRecord->position_id);
        $autofillValues = [
            'autofill_name'       => trim(($userRecord->firstname ?? '') . ' ' . ($userRecord->lastname ?? '')) ?: ($userRecord->name ?? ''),
            'autofill_position'   => $position->name ?? '',
            'autofill_department' => $userRecord->department ?? $userRecord->office ?? '',
            'autofill_date'       => Carbon::now()->format('F d, Y'),
        ];

        // Build field definitions for the form
        $fields = $template->fields->map(fn($f) => [
            'id'           => $f->id,
            'cell_ref'     => $f->cell_ref,
            'field_type'   => $f->field_type,
            'field_label'  => $f->field_label,
            'field_options' => $f->field_options,
            'is_required'  => $f->is_required,
            'current_value' => $f->isAutofill()
                ? ($autofillValues[$f->field_type] ?? '')
                : ($answers[$f->id]?->value ?? ''),
        ])->toArray();

        $htmlTable = $this->parser->toHtmlTable($parsed, $fields, false);

        $positionsList = DB::table('positions')->where('is_active', true)->orderBy('name')->pluck('name')->toArray();

        return response()->json([
            'template'      => ['id' => $template->id, 'name' => $template->name],
            'submission_id' => $submission->id,
            'html_table'    => $htmlTable,
            'fields'        => $fields,
            'autofill'      => $autofillValues,
            'positions'     => $positionsList,
        ]);
    }

    /**
     * Save a draft (AJAX).
     */
    public function saveDraft(Request $request, int $templateId)
    {
        $sessionUser = session('user');
        $userId      = $sessionUser['id'] ?? 0;

        $submission = IpcrfSubmission::where('template_id', $templateId)
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->firstOrFail();

        $answers = $request->input('answers', []);
        foreach ($answers as $fieldId => $value) {
            SubmissionAnswer::updateOrCreate(
                ['submission_id' => $submission->id, 'template_field_id' => (int)$fieldId],
                ['value' => $value]
            );
        }

        return response()->json(['success' => true, 'message' => 'Draft saved!']);
    }

    /**
     * Submit a completed form.
     */
    public function submit(Request $request, int $templateId)
    {
        $sessionUser = session('user');
        $userId      = $sessionUser['id'] ?? 0;

        $submission = IpcrfSubmission::where('template_id', $templateId)
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->firstOrFail();

        // Save any last answers
        $answers = $request->input('answers', []);
        foreach ($answers as $fieldId => $value) {
            SubmissionAnswer::updateOrCreate(
                ['submission_id' => $submission->id, 'template_field_id' => (int)$fieldId],
                ['value' => $value]
            );
        }

        $submission->update([
            'status'       => IpcrfSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        AuditService::log('submission_submitted', $userId, 'IpcrfSubmission', $submission->id);

        return response()->json(['success' => true, 'message' => 'Form submitted successfully!', 'submission_id' => $submission->id]);
    }

    /**
     * Handle AJAX picture upload for form-filling.
     */
    public function uploadPicture(Request $request, int $submissionId, int $fieldId)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB limit
        ]);

        $submission = IpcrfSubmission::findOrFail($submissionId);
        
        // Verify owner or admin
        $sessionUser = session('user');
        $isAdmin = $sessionUser && ($sessionUser['role'] ?? '') === 'admin';
        $isOwner = ($submission->user_id ?? 0) === ($sessionUser['id'] ?? 0);
        
        if (!$isOwner && !$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $file = $request->file('file');
        $publicDir = public_path('storage/ipcrf_images');
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = uniqid('user_img_', true) . '.' . $extension;
        $file->move($publicDir, $filename);

        return response()->json([
            'success' => true,
            'url'     => asset('storage/ipcrf_images/' . $filename),
        ]);
    }

    /**
     * Download a completed XLSX for an approved submission.
     */
    public function download(int $submissionId)
    {
        $sessionUser = session('user');
        $submission  = IpcrfSubmission::with(['user.position', 'template', 'answers.field'])
            ->where('user_id', $sessionUser['id'] ?? 0)
            ->findOrFail($submissionId);

        AuditService::log('user_download', $sessionUser['id'] ?? null, 'IpcrfSubmission', $submissionId);
        return $this->xlsxGenerator->download($submission);
    }

    public function submissionHistory()
    {
        $sessionUser = session('user');
        $submissions = IpcrfSubmission::with('template')
            ->where('user_id', $sessionUser['id'] ?? 0)
            ->latest()
            ->get();
        return response()->json(['submissions' => $submissions]);
    }
}
