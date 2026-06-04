<?php
define('LARAVEL_START', microtime(true));

require 'c:\Users\visma\IPCRFmanagement2\vendor\autoload.php';
$app = require_once 'c:\Users\visma\IPCRFmanagement2\bootstrap\app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\IpcrfSubmission;
use App\Models\IpcrfTemplate;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    $user = User::where('role', 'staff')->first();
    if (!$user) {
        // Fallback to first user
        $user = User::first();
    }
    if (!$user) {
        throw new \Exception("No user found to run simulation.");
    }

    $template = IpcrfTemplate::first();
    if (!$template) {
        throw new \Exception("No IPCRF Template found. Please run template creation first or seed data.");
    }

    echo "--- SIMULATION START ---\n";
    echo "User: " . $user->name . " (Role: " . $user->role . ", Email: " . $user->email . ")\n";
    echo "Template: " . $template->name . "\n";

    // 1. Create a draft submission
    echo "\n1. Creating Draft Submission...\n";
    $submission = IpcrfSubmission::create([
        'user_id' => $user->id,
        'template_id' => $template->id,
        'status' => IpcrfSubmission::STATUS_DRAFT,
        'submitted_at' => null,
    ]);
    echo "Created Submission ID: " . $submission->id . " | Status: " . $submission->status . " (" . $submission->statusLabel() . ")\n";

    // 2. Submit the form (Staff action)
    echo "\n2. Submitting form (Staff action)...\n";
    $submission->update([
        'status' => IpcrfSubmission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);
    $submission->refresh();
    echo "Submission Status: " . $submission->status . " (" . $submission->statusLabel() . ")\n";
    if ($submission->status !== 'submitted') {
        throw new \Exception("Status should be submitted.");
    }

    // 3. POO Admin Approve
    echo "\n3. Approving form (POO Admin action)...\n";
    if (!in_array($submission->status, ['submitted', 'under_review'], true)) {
        throw new \Exception("POO Admin should only approve submitted or under_review forms.");
    }
    $submission->update([
        'status' => IpcrfSubmission::STATUS_POO_APPROVED,
        'reviewed_at' => now(),
        'reviewed_by' => $user->id, // use valid user ID
    ]);
    $submission->refresh();
    echo "Submission Status: " . $submission->status . " (" . $submission->statusLabel() . ")\n";
    if ($submission->status !== 'poo_approved') {
        throw new \Exception("Status should be poo_approved.");
    }

    // 4. RPMO Admin Approve (Final stage)
    echo "\n4. Approving form (RPMO Admin action - Final stage)...\n";
    if (!in_array($submission->status, ['poo_approved', 'under_review'], true)) {
        throw new \Exception("RPMO Admin should only approve poo_approved or under_review forms.");
    }
    $submission->update([
        'status' => IpcrfSubmission::STATUS_APPROVED,
        'reviewed_at' => now(),
        'reviewed_by' => $user->id, // use valid user ID
    ]);
    $submission->refresh();
    echo "Submission Status: " . $submission->status . " (" . $submission->statusLabel() . ")\n";
    if ($submission->status !== 'approved') {
        throw new \Exception("Status should be approved.");
    }

    echo "\n--- SIMULATION SUCCESSFUL ---\n";
    DB::rollBack();
    echo "Transaction rolled back cleanly.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n[ERROR] Simulation failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
