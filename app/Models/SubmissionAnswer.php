<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnswer extends Model
{
    protected $fillable = ['submission_id', 'template_field_id', 'value'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(IpcrfSubmission::class, 'submission_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(TemplateField::class, 'template_field_id');
    }
}
