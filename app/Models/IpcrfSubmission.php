<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpcrfSubmission extends Model
{
    protected $fillable = [
        'template_id', 'user_id', 'status', 'admin_remarks',
        'submitted_at', 'reviewed_at', 'reviewed_by', 'generated_file_path',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public const STATUS_DRAFT       = 'draft';
    public const STATUS_SUBMITTED   = 'submitted';
    public const STATUS_RPMO_APPROVED = 'rpmo_approved';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED    = 'approved';
    public const STATUS_REJECTED    = 'rejected';

    public function template(): BelongsTo
    {
        return $this->belongsTo(IpcrfTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class, 'submission_id');
    }

    public function getAnswerForField(int $fieldId): ?SubmissionAnswer
    {
        return $this->answers->where('template_field_id', $fieldId)->first();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft'        => 'badge-draft',
            'submitted'    => 'badge-submitted',
            'rpmo_approved' => 'badge-rpmo-approved',
            'under_review' => 'badge-review',
            'approved'     => 'badge-approved',
            'rejected'     => 'badge-rejected',
            default        => 'badge-draft',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'        => 'Draft',
            'submitted'    => 'Submitted (Pending RPMO)',
            'rpmo_approved'=> 'Approved by RPMO (Pending POO)',
            'under_review' => 'Under Review',
            'approved'     => 'Approved & Sealed',
            'rejected'     => 'Rejected',
            default        => 'Unknown',
        };
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }
}
