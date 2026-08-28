<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCommunity;

class Payment extends Model
{
    use HasFactory, Auditable, BelongsToCommunity;

    protected $fillable = [
        'resident_id',
        'fee_id',
        'fund_id',
        'amount',
        'method',    // cash | bank_transfer | cheque | mobile_money | other
        'paid_at',
        'note',
        'status',    // PAID | PENDING | VOID
        'period_key',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function auditLabel(): string
    {
        $resident = $this->relationLoaded('resident') ? $this->resident : $this->resident()->first();

        return ($resident->name ?? 'resident #'.$this->resident_id).' — '.number_format((float) $this->amount, 2);
    }
}
