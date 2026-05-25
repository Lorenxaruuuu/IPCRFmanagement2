<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'municipality_id', 'code'];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
