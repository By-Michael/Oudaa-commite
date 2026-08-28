<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\Auditable;

/**
 * The single authenticated actor in this system: a committee member.
 * There is no "resident" login — residents are just records the
 * committee manages, never users of the panel.
 *
 * Multiple committee members can each have their own account (see
 * MemberController) so audit log entries can be attributed to a
 * specific person rather than a single shared login.
 */
class Committee extends Authenticatable
{
    use Notifiable, Auditable;

    protected $table = 'committees';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function auditLabel(): string
    {
        return "{$this->name} <{$this->email}>";
    }
}
