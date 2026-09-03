<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlowQuery extends Model
{
    public $timestamps = false;
    protected $fillable = ['sql', 'bindings', 'time_ms', 'path', 'created_at'];

    protected function casts(): array
    {
        return ['bindings' => 'array'];
    }

    protected static function booted()
    {
        static::creating(function ($m) {
            $m->created_at = $m->created_at ?? now();
        });
    }
}
