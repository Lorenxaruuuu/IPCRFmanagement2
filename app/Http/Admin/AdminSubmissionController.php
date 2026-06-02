<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpcrfSubmission;
use App\Services\XlsxGeneratorService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AdminSubmissionController extends Controller
{
    public function __construct(private XlsxGeneratorService $xlsxGenerator) {}

    public function index(Request $request)
    {
        $query = IpcrfSubmission::with(['user.position', 'template', 'reviewer'])
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

    public function show(int $id)
    {
        $submission = IpcrfSubmission::with([
            'user.position',
            'template.fields',
            'answers.field',
            'reviewer',
        ])->findOrFail($id);

        return response()->json(['submission' => $submission]);
    }

    public function approve(Request $request, int $id)
    {
        $submission = IpcrfSubmission::findOrFail($id);
        $submission->update([
            'status'      => IpcrfSubmission::STATUS_APPROVED,
            'admin_remarks' => $request->remarks,
            'reviewed_at' => now(),
            'reviewed_by' => session('user')['id'] ?? null,
        ]);

        AuditService::log('submission_approved', null, 'IpcrfSubmission', $id, [
            'remarks' => $request->remarks,
        ]);

        return response()->json(['success' => true, 'message' => 'Submission approved!']);
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

        return response()->json(['success' => true, 'message' => 'Submission rejected.']);
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
