<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'lastname',
    'firstname',
    'name',
    'employee_id',
    'email',
    'password',
    'role',
    'position',
    'requested_position_id',
    'approved',
    'profile_edited',
    'birthday',
    'gender',
    'address',
    'region',
    'position_id',
    'department',
    'office',
    'assigned_province',
];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function requestedPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'requested_position_id');
    }

    /** Job title / designation (avoids conflict with admin `position` column: rpmo, poo, etc.) */
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function adminPositionType(): string
    {
        $raw = $this->getAttributes()['position'] ?? null;

        return is_string($raw) && in_array($raw, ['rpmo', 'poo', 'rpmo_poo', 'none'], true)
            ? ($raw === 'none' ? 'rpmo' : $raw)
            : 'rpmo';
    }

    public function submissions()
    {
        return $this->hasMany(IpcrfSubmission::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? '')) ?: ($this->name ?? '');
    }
}
