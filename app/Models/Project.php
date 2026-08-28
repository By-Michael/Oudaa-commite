<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCommunity;

class Project extends Model
{
    use HasFactory, Auditable, BelongsToCommunity;

    protected $fillable = [
        'name',
        'description',
        'fund_id',
        'planned_budget',
        'start_date',
        'end_date',
        'status', // planned | active | completed | archived
    ];

    protected $casts = [
        'planned_budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function spent(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function remaining(): float
    {
        return (float) $this->planned_budget - $this->spent();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'active']);
    }
}
