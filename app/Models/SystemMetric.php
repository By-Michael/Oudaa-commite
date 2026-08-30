<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    public $timestamps = false;
    protected $fillable = ['duration_ms', 'status_code', 'method', 'path', 'created_at'];

    protected static function booted()
    {
        static::creating(function ($m) {
            $m->created_at = $m->created_at ?? now();
        });
    }
}
