<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCommunity;

class Resident extends Model
{
    use HasFactory, Auditable, BelongsToCommunity;

    protected $fillable = [
        'name',
        'id_number',
        'unit_number',
        'block_number',
        'phone',
        'email',
        'occupancy', // owner | renter
        'status',    // active | inactive
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function auditLabel(): string
    {
        return "{$this->name} (Unit {$this->unit_number})";
    }
}
