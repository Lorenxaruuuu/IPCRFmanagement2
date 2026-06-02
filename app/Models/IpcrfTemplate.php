<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpcrfTemplate extends Model
{
    protected $fillable = [
        'name', 'description', 'file_path', 'file_name', 'file_original_name',
        'sheet_data', 'merged_cells', 'total_rows', 'total_cols',
        'is_active', 'uploaded_by', 'semester', 'form_specification',
    ];

    protected $casts = [
        'sheet_data'   => 'array',
        'merged_cells' => 'array',
        'is_active'    => 'boolean',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'template_positions', 'template_id', 'position_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class, 'template_id')->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(IpcrfSubmission::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
