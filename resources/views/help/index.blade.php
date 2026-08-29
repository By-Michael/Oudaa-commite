@extends('layouts.app')
@section('title', 'Help and Support')
@section('content')

<div class="panel">
    <div class="panel-head"><h2>Frequently Asked Questions</h2></div>
    <div class="panel-body">
        <div class="faq-search-wrap">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="faq-search" placeholder="Search — e.g. &quot;payment&quot;, &quot;fund&quot;, &quot;employee&quot;…" autocomplete="off">
        </div>
        <p id="faq-no-results" class="muted" style="display:none;">No matching questions — try different words, or message us on Telegram below.</p>
    </div>
    <div class="panel-body faq-list" id="faq-list">

        <details class="faq-item" open>
            <summary>How is the app organized?</summary>
            <div class="faq-answer">
                Everything lives behind the sidebar on the left: <strong>Dashboard</strong> (a quick
                overview of funds, recent payments and expenses), <strong>Residents</strong>,
                <strong>Fees</strong>, <strong>Payments</strong>, <strong>Funds</strong>,
                <strong>Projects</strong>, <strong>Expenses</strong>, <strong>Employees</strong>, and the
                <strong>Audit Log</strong> (a read-only history of who changed what). Your account settings
                are under the avatar menu in the top-right corner.
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I add a resident?</summary>
            <div class="faq-answer">
                Go to <strong>Residents</strong> and click <strong>+ Add Resident</strong>. Fill in their
                name, unit number, ID number, and whether they're an <strong>Owner</strong> or
                <strong>Tenant</strong>, then save. New residents start as Active. To edit someone or change
                their status later, use the <strong>Edit</strong> and <strong>Deactivate / Activate</strong>
                buttons on their row — residents are never deleted, only deactivated, so their payment
                history is never lost.
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I set up a fee?</summary>
            <div class="faq-answer">
                Go to <strong>Fees</strong> and click <strong>+ Add Fee</strong>. Give it a name, an amount
                (in ETB), and link it to a <strong>Fund</strong> — that's the fund payments against this
                fee will land in. Use the <strong>Unpaid</strong> link on any fee to see which residents
                still owe it.
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I record a payment?</summary>
            <div class="faq-answer">
                Go to <strong>Payments</strong> and click <strong>+ Record Payment</strong>. Start typing
                the resident's name, unit, or ID number in the search box and pick them from the list that
                appears. Then choose either a <strong>Fee</strong> (the amount fills in automatically) or a
                <strong>Fund</strong> to pay into directly — you can only choose one, picking one locks the
                other. New payments are recorded as Paid; to correct the status afterwards, open it from the
                list and use <strong>Edit Status</strong>.
            </div>
        </details>

        <details class="faq-item">
            <summary>What's the difference between a Fund and a Project?</summary>
            <div class="faq-answer">
                A <strong>Fund</strong> is a running pool of money (e.g. "Maintenance", "Reserve") that
                payments and expenses move in and out of — its balance is always the sum of what's come in
                minus what's gone out. A <strong>Project</strong> is a specific initiative with its own
                planned budget, linked to a fund, that you track spending against over time (e.g. "Roof
                repair 2026").
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I log an expense?</summary>
            <div class="faq-answer">
                Go to <strong>Expenses</strong> and click <strong>+ Record Expense</strong>. Pick the fund
                (and, if relevant, the project or employee it relates to) and enter the amount in ETB.
                Expenses reduce the balance of the fund they're recorded against.
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I manage employees and their pay?</summary>
            <div class="faq-answer">
                Go to <strong>Employees</strong> to add staff, set their salary and payment date, or
                terminate/reactivate them. Opening an employee shows their full salary payment history, and
                you can log a new salary payment straight from there.
            </div>
        </details>

        <details class="faq-item">
            <summary>What is the Audit Log for?</summary>
            <div class="faq-answer">
                It's a read-only record of every meaningful change made in the panel — who added, edited, or
                deactivated what, and when. Nothing can be edited or deleted from it; it's there so the
                committee always has a clear trail of accountability.
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I invite another committee member?</summary>
            <div class="faq-answer">
                Go to <strong>Members</strong> (in the account menu) and click <strong>+ Add Committee
                Member</strong> to create a login for them. Each member has their own account, so audit log
                entries are always attributed to the person who actually made the change.
            </div>
        </details>

        <details class="faq-item">
            <summary>I forgot my password — what do I do?</summary>
            <div class="faq-answer">
                On the sign-in page, click <strong>Forgot password?</strong> and enter your email. If it's
                registered, you'll get an emailed link to set a new password. The link expires after 60
                minutes for security.
            </div>
        </details>

        <details class="faq-item">
            <summary>Will I get logged out while I'm working?</summary>
            <div class="faq-answer">
                No — as long as this tab stays open, the app quietly keeps your session alive in the
                background. You'll only be signed out when you deliberately click <strong>Log out</strong>,
                or if the tab has been closed and left inactive for a long stretch.
            </div>
        </details>

        <details class="faq-item">
            <summary>Why does something ask me to confirm before I can do it?</summary>
            <div class="faq-answer">
                Actions like deactivating a resident, archiving a fund, or terminating an employee show a
                small confirmation popup first, since they change something's status. Just confirm or
                cancel in that popup — nothing happens until you do.
            </div>
        </details>

    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Need more help or info?</h2></div>
    <div class="panel-body">
        <p class="muted" style="margin-top:0;">
            Didn't find what you needed above? Reach out directly on Telegram and we'll help you out.
        </p>
        <a href="https://t.me/mikoz_124" target="_blank" rel="noopener" class="btn btn-primary">
            Message us on Telegram — @mikoz_124
        </a>
    </div>
</div>

<style>
.faq-list{display:flex;flex-direction:column;gap:4px;}
.faq-item{border-bottom:1px solid var(--md-outline-variant,#CAC4D0);padding:12px 0;}
.faq-item:last-child{border-bottom:none;}
.faq-item summary{
    cursor:pointer; font-weight:600; font-size:14.5px; color:var(--md-on-bg,#1C1B1F);
    list-style:none; display:flex; align-items:center; gap:8px;
}
.faq-item summary::-webkit-details-marker{display:none;}
.faq-item summary::before{
    content:'+'; display:inline-flex; align-items:center; justify-content:center;
    width:20px; height:20px; border-radius:50%; background:var(--md-surface-container-high,#ECE6F0);
    font-size:14px; color:var(--md-primary,#6750A4); flex-shrink:0;
}
.faq-item[open] summary::before{content:'−';}
.faq-answer{margin-top:10px; padding-left:28px; font-size:13.5px; line-height:1.6; color:var(--md-on-surface-variant,#49454F);}

.faq-search-wrap{
    display:flex; align-items:center; gap:10px;
    background:var(--md-surface-container, #F3EDF7); border-radius:9999px;
    padding:10px 16px; color:var(--md-on-surface-variant,#49454F);
    margin:0 0 4px;
}
.faq-search-wrap input{
    border:none; background:transparent; outline:none; flex:1;
    font-size:13.5px; font-family:inherit; color:var(--md-on-bg,#1C1B1F);
}
.faq-item.is-hidden{display:none;}
</style>

<script>
(function () {
    var input = document.getElementById('faq-search');
    var items = Array.prototype.slice.call(document.querySelectorAll('#faq-list .faq-item'));
    var noResults = document.getElementById('faq-no-results');
    if (!input || !items.length) return;

    // Keep every item's default open/closed state so clearing the search
    // restores exactly what the page loaded with.
    items.forEach(function (item) {
        item.dataset.defaultOpen = item.hasAttribute('open') ? '1' : '0';
    });

    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var visibleCount = 0;

        items.forEach(function (item) {
            var text = item.textContent.toLowerCase();
            var matches = !q || text.indexOf(q) !== -1;
            item.classList.toggle('is-hidden', !matches);
            if (matches) visibleCount++;
            // While searching, auto-expand matches so the answer is visible
            // without an extra click; restore normal state once cleared.
            if (q) {
                item.open = matches;
            } else {
                item.open = item.dataset.defaultOpen === '1';
            }
        });

        noResults.style.display = (q && visibleCount === 0) ? 'block' : 'none';
    });
})();
</script>

@endsection
