<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class Expense extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'fund_id',
        'project_id',
        'employee_id',
        'category',
        'amount',
        'vendor',
        'incurred_at',
        'note',
        'receipt_path',
    ];

    protected $casts = [
        'incurred_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function receiptUrl(): ?string
    {
        return $this->receipt_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->receipt_path) : null;
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function auditLabel(): string
    {
        return "{$this->category} — ".number_format((float) $this->amount, 2);
    }
}
