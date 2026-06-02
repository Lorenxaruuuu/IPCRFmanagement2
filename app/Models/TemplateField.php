<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateField extends Model
{
    protected $fillable = [
        'template_id', 'cell_ref', 'sheet_index', 'row_index', 'col_index',
        'field_type', 'field_label', 'field_options', 'is_required', 'sort_order',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required'   => 'boolean',
    ];

    public const FIELD_TYPES = [
        'autofill_name'       => 'Auto-Fill Employee Name',
        'autofill_position'   => 'Auto-Fill Position/Designation',
        'autofill_department' => 'Auto-Fill Office/Department',
        'autofill_date'       => 'Auto-Fill Date Signed',
        'text'                => 'Text Input',
        'number'              => 'Number Input',
        'textarea'            => 'Text Area',
        'rating'              => 'Rating Input',
        'dropdown'            => 'Dropdown',
        'signature'           => 'Signature Field',
        'readonly'            => 'Read-Only Label',
        'picture'             => 'Add Picture',
    ];

    public const AUTOFILL_TYPES = [
        'autofill_name', 'autofill_position', 'autofill_department', 'autofill_date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(IpcrfTemplate::class, 'template_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class, 'template_field_id');
    }

    public function isAutofill(): bool
    {
        return in_array($this->field_type, self::AUTOFILL_TYPES);
    }
}
