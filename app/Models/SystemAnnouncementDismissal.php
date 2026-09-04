<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAnnouncementDismissal extends Model
{
    public $timestamps = false;

    protected $fillable = ['system_announcement_id', 'committee_id', 'dismissed_at'];

    protected $casts = [
        'dismissed_at' => 'datetime',
    ];
}
