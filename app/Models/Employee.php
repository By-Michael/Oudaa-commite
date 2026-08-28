<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class Employee extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'id_number',
        'role',
        'salary',
        'payment_date',
        'phone',
        'status', // active | terminated
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Salary payments logged against this employee, via the shared
     * expenses ledger (category = "Salary", employee_id set). Keeping
     * salary spend inside `expenses` means it's counted in fund
     * balances the same way any other outgoing money is, instead of
     * living in a separate, easy-to-forget system.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function auditLabel(): string
    {
        return "{$this->name} ({$this->role})";
    }
}
