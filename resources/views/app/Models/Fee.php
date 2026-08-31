<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCommunity;

class Fee extends Model
{
    use HasFactory, Auditable, BelongsToCommunity;

    protected $fillable = [
        'name',
        'fund_id',
        'amount',
        'frequency', // monthly | quarterly | yearly | one_time
        'recurrence_day', // day of month (1-31) this fee recurs on, optional
        'status',    // active | inactive
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Total collected for this fee only, within a given period key
     * (e.g. "2026-03"). Falls back to all-time if no period is given.
     */
    public function collectedForPeriod(?string $periodKey = null): float
    {
        $query = $this->payments()->where('status', 'PAID');

        if ($periodKey) {
            $query->where('period_key', $periodKey);
        }

        return (float) $query->sum('amount');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * The day of month this fee recurs on. Falls back to the day of
     * month the fee was created on if none was explicitly set.
     */
    public function recurrenceDay(): int
    {
        return (int) ($this->recurrence_day ?? $this->created_at?->day ?? now()->day);
    }

    /**
     * The key identifying the "current period" for this fee, used to
     * decide whether a resident has already paid for the period they're
     * currently in. one_time fees have a single, permanent period.
     */
    public function currentPeriodKey(): string
    {
        $now = now();

        return match ($this->frequency) {
            'monthly' => $now->format('Y-m'),
            'quarterly' => $now->year.'-Q'.ceil($now->month / 3),
            'yearly' => (string) $now->year,
            default => 'once',
        };
    }
}
