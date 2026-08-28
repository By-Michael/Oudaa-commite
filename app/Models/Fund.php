<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCommunity;

class Fund extends Model
{
    use HasFactory, Auditable, BelongsToCommunity;

    protected $fillable = [
        'name',
        'category',
        'description',
        'status', // active | archived
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function balance(): float
    {
        $in = (float) $this->payments()->where('status', 'PAID')->sum('amount');
        $out = (float) $this->expenses()->sum('amount');

        return $in - $out;
    }
}
