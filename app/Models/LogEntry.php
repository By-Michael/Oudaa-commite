<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    public $timestamps = false;

    protected $fillable = ['level', 'message', 'context', 'created_at'];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function (self $entry) {
            $entry->created_at = $entry->created_at ?? now();
        });
    }
}
