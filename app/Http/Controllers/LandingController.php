<?php

namespace App\Http\Controllers;

/**
 * Static marketing pages, converted from the Nexora template into
 * Blade. No models/DB involved — these live on the central connection
 * context (i.e. outside any {tenant} group) since they're the same
 * for every visitor regardless of which community they end up creating.
 */
class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index');
    }

    public function about()
    {
        return view('landing.about');
    }

    public function services()
    {
        return view('landing.services');
    }

    public function serviceDetails(string $service)
    {
        $features = [
            'residents' => [
                'title' => 'Residents',
                'icon' => 'bi-people',
                'intro' => "A committee's most basic job is knowing who actually lives in the community — and that's harder than it sounds once it's spread across old spreadsheets, WhatsApp threads and paper forms.",
                'body' => "Oudaa keeps one record per household: name, ID number, unit number (plus block number, for condo/apartment communities), phone, email, and whether they're an owner or a tenant. Search finds a resident instantly by any of those fields, and residents are deactivated rather than deleted when they move out — so payment history always stays intact.",
                'points' => [
                    ['icon' => 'bi-search', 'title' => 'Instant search', 'desc' => 'Find anyone by name, unit, block or ID number.'],
                    ['icon' => 'bi-building', 'title' => 'Condo-ready', 'desc' => 'Block numbers appear automatically for condo communities.'],
                    ['icon' => 'bi-toggle-on', 'title' => 'Active/inactive status', 'desc' => 'Deactivate a resident without losing their history.'],
                    ['icon' => 'bi-person-badge', 'title' => 'Owner or tenant', 'desc' => 'Track occupancy type per household.'],
                ],
            ],
            'fees' => [
                'title' => 'Fees',
                'icon' => 'bi-receipt',
                'intro' => "Community fees are usually the single most disputed line item — who owes what, and since when.",
                'body' => "Set up fees as recurring (monthly, quarterly) or one-off charges, tied to a specific fund. Every fee shows exactly which residents have paid and which haven't, so there's no more relying on memory or a scattered paper trail come collection time.",
                'points' => [
                    ['icon' => 'bi-arrow-repeat', 'title' => 'Recurring or one-off', 'desc' => 'Set the cadence that matches your community.'],
                    ['icon' => 'bi-list-check', 'title' => 'Unpaid list, one click away', 'desc' => 'See exactly who still owes for any fee.'],
                    ['icon' => 'bi-toggle-off', 'title' => 'Activate / deactivate', 'desc' => 'Retire old fees without deleting their history.'],
                ],
            ],
            'payments' => [
                'title' => 'Payments',
                'icon' => 'bi-cash-coin',
                'intro' => "A fee only means something once it's actually reconciled against real payments.",
                'body' => "Every payment gets recorded against the resident and fee it belongs to, building a clean, auditable trail the whole committee can trust — not a shared notebook or a spreadsheet only one person understands.",
                'points' => [
                    ['icon' => 'bi-link-45deg', 'title' => 'Linked to fee & resident', 'desc' => 'Every payment traces back to exactly what it was for.'],
                    ['icon' => 'bi-clock-history', 'title' => 'Full history', 'desc' => 'Nothing gets edited away — see every payment ever recorded.'],
                ],
            ],
            'funds' => [
                'title' => 'Funds',
                'icon' => 'bi-piggy-bank',
                'intro' => "Communities rarely run on one pool of money — maintenance, reserve, events, and sometimes more.",
                'body' => "Funds keep those pools separate and clearly labeled, so nobody accidentally spends the reserve fund on a birthday party. Fees, projects and expenses all attach to a specific fund, so every balance stays accurate.",
                'points' => [
                    ['icon' => 'bi-collection', 'title' => 'Multiple funds', 'desc' => 'Maintenance, reserve, events — however you organize it.'],
                    ['icon' => 'bi-archive', 'title' => 'Archive, don\'t delete', 'desc' => 'Retire an old fund while keeping its history intact.'],
                ],
            ],
            'projects' => [
                'title' => 'Projects',
                'icon' => 'bi-kanban',
                'intro' => "Repaving the parking lot. Replacing a roof. Every community eventually has a project that needs its own budget line.",
                'body' => "Plan a project against a specific fund, track its status from planned through active to completed, and see every expense logged against it — so at any point the committee knows exactly what a project has cost so far.",
                'points' => [
                    ['icon' => 'bi-flag', 'title' => 'Status tracking', 'desc' => 'Planned → active → completed → archived.'],
                    ['icon' => 'bi-wallet2', 'title' => 'Tied to a fund', 'desc' => 'Every project draws against a specific fund\'s budget.'],
                ],
            ],
            'expenses' => [
                'title' => 'Expenses',
                'icon' => 'bi-wallet2',
                'intro' => "Money leaving the community's accounts needs the same discipline as money coming in.",
                'body' => "Log every expense against the right project or fund with a receipt attached, so the committee — and any resident who asks — can always see exactly where the money went.",
                'points' => [
                    ['icon' => 'bi-receipt-cutoff', 'title' => 'Receipt attached', 'desc' => 'Keep proof alongside every recorded expense.'],
                    ['icon' => 'bi-diagram-2', 'title' => 'Tied to project or fund', 'desc' => 'Every cost traces back to what it paid for.'],
                ],
            ],
        ];

        $feature = $features[$service] ?? $features['residents'];
        $feature['slug'] = array_key_exists($service, $features) ? $service : 'residents';

        return view('landing.service_details', ['feature' => $feature, 'allFeatures' => $features]);
    }

    public function contact()
    {
        return view('landing.contact');
    }
}
