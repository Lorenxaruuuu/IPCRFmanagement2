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
        // sheet_data is stored on disk, not in DB — see accessor/mutator below
        'merged_cells' => 'array',
        'is_active'    => 'boolean',
    ];

    /**
     * Path to the compressed sheet_data file for this template.
     */
    private function sheetDataPath(): string
    {
        return storage_path('app/private/ipcrf_sheet_data/' . $this->id . '.json.gz');
    }

    /**
     * Save sheet_data array to a gzip-compressed file on disk.
     * Call this explicitly after create() when $this->id is available.
     */
    public function saveSheetData(array $data): void
    {
        $dir = storage_path('app/private/ipcrf_sheet_data');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->sheetDataPath(), gzcompress(json_encode($data), 6));
    }

    /**
     * Read sheet_data from disk. Falls back to DB column for old records.
     */
    public function getSheetDataAttribute(): ?array
    {
        if ($this->id && file_exists($this->sheetDataPath())) {
            $compressed = file_get_contents($this->sheetDataPath());
            $json = @gzuncompress($compressed);
            return $json ? json_decode($json, true) : null;
        }
        // Backward-compat: try reading from DB column
        $raw = $this->attributes['sheet_data'] ?? null;
        return $raw ? json_decode($raw, true) : null;
    }

    /**
     * When sheet_data is set via Eloquent (e.g. update(['sheet_data' => ...])),
     * write it to disk and store null in the DB column.
     */
    public function setSheetDataAttribute($value): void
    {
        // Always keep null in DB — actual data lives on disk
        $this->attributes['sheet_data'] = null;

        if ($value !== null && $this->id) {
            $this->saveSheetData(is_array($value) ? $value : json_decode($value, true) ?? []);
        }
    }

    /**
     * Delete the sheet_data file when the template is deleted.
     */
    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (self $template) {
            if ($template->id && file_exists($template->sheetDataPath())) {
                @unlink($template->sheetDataPath());
            }
        });
    }

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
