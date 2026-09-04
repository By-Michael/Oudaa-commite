<?php

namespace App\Http\Controllers;

use App\Models\SystemAnnouncement;
use App\Models\SystemAnnouncementDismissal;

class SystemAnnouncementController extends Controller
{
    /**
     * "Ignore" — records that this committee member has seen it, so it
     * stops showing for them specifically. Doesn't touch the
     * announcement itself: other committee members it was pushed to
     * (across other communities on this instance) still see it until
     * they dismiss it too, or the admin dashboard disables/deletes it.
     */
    public function dismiss(string $tenant, SystemAnnouncement $announcement)
    {
        SystemAnnouncementDismissal::firstOrCreate(
            ['system_announcement_id' => $announcement->id, 'committee_id' => auth()->id()],
            ['dismissed_at' => now()]
        );

        return back();
    }
}
