<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(IpcrfTemplate::class, 'template_positions', 'position_id', 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
