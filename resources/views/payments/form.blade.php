@extends('layouts.app')
@section('title', 'Record Payment')
@section('content')

@php
    use Illuminate\Support\Js;
@endphp

<div class="panel" style="max-width:640px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('payments.store') }}" id="payment-form">
            @csrf

            <div class="form-row" style="position:relative;">
                <label>Resident</label>
                <input
                    type="text"
                    id="resident-search"
                    autocomplete="off"
                    placeholder="Type a name, unit, or ID number to search…"
                    value="{{ old('resident_search') }}"
                >
                <input type="hidden" name="resident_id" id="resident_id" value="{{ old('resident_id') }}">
                <div id="resident-results" class="resident-results"></div>
                @error('resident_id') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-grid">
                <div class="form-row lock-hover-wrap" id="fee-wrap">
                    <label>Fee</label>
                    <select name="fee_id" id="fee_id">
                        <option value="">No fee — pay into a fund directly</option>
                        @foreach ($fees as $fee)
                            <option value="{{ $fee->id }}" data-amount="{{ $fee->amount }}" data-fund="{{ $fee->fund_id }}" @selected(old('fee_id') == $fee->id)>
                                {{ $fee->name }} ({{ money($fee->amount) }}) — {{ $fee->fund->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="hover-hint">Only one of Fee or Fund can be chosen.</span>
                </div>
                <div class="form-row lock-hover-wrap" id="fund-wrap">
                    <label>Fund</label>
                    <select name="fund_id" id="fund_id">
                        <option value="">Select a fund…</option>
                        @foreach ($funds as $fund)
                            <option value="{{ $fund->id }}" @selected(old('fund_id') == $fund->id)>{{ $fund->name }}</option>
                        @endforeach
                    </select>
                    <span class="hover-hint">Only one of Fee or Fund can be chosen.</span>
                </div>
            </div>
            <p class="muted" style="font-size:12px;margin:-10px 0 18px;">
                Choose a fee, or a fund directly — at least one is required. Selecting one locks the other.
            </p>
            @error('fee_id') <div class="field-error" style="margin-top:-10px;">{{ $message }}</div> @enderror

            <div class="form-grid">
                <div class="form-row lock-hover-wrap" id="amount-wrap">
                    <label>Amount (ETB)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" required>
                    <span class="hover-hint">The amount will be the fee amount set in the selected fee.</span>
                </div>
                <div class="form-row">
                    <label>Date Paid</label>
                    <input type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" required>
                </div>
            </div>

            <div class="form-row">
                <label>Method</label>
                <select name="method">
                    @foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'mobile_money' => 'Mobile Money', 'other' => 'Other'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('method') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <label>Note</label>
                <input type="text" name="note" value="{{ old('note') }}">
            </div>

            <p class="muted" style="font-size:12px;margin:0 0 18px;">
                New payments are recorded as Paid. To mark something Pending or Void, record it here first, then edit it from the payments list.
            </p>

            <button type="submit" class="btn btn-primary">Record Payment</button>
            <a href="{{ route('payments.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

<style>
.resident-results{
    position:fixed; z-index:1100;
    background:var(--md-surface-container-high, #ECE6F0);
    border-radius:var(--md-r-sm, 12px);
    box-shadow:var(--md-shadow-md, 0 4px 14px rgba(0,0,0,.15));
    max-height:260px; overflow-y:auto; display:none; margin-top:4px;
}
.resident-results.open{display:block}
.resident-results .opt{
    padding:10px 14px; cursor:pointer; font-size:13.5px; border-bottom:1px solid var(--md-outline-variant, #CAC4D0);
}
.resident-results .opt:last-child{border-bottom:none}
.resident-results .opt:hover, .resident-results .opt.active{background:rgba(103,80,164,.12)}
.resident-results .opt small{display:block;color:var(--md-on-surface-variant,#49454F);font-size:11.5px;margin-top:2px}
.resident-results .empty{padding:10px 14px;font-size:13px;color:var(--md-on-surface-variant,#49454F)}
.resident-results .exact-match{border-left:3px solid var(--md-primary, #6750A4)}

.lock-hover-wrap{position:relative;}
.lock-hover-wrap .hover-hint{
    display:none;
    position:absolute; left:0; top:100%; margin-top:4px; z-index:15;
    background:var(--md-surface-container-high, #ECE6F0);
    border-radius:var(--md-r-sm, 8px);
    box-shadow:var(--md-shadow-md, 0 4px 14px rgba(0,0,0,.15));
    padding:6px 10px; font-size:11.5px; color:var(--md-on-surface-variant,#49454F);
    white-space:normal; max-width:280px;
}
.lock-hover-wrap.is-locked:hover .hover-hint{display:block;}
.lock-hover-wrap.is-locked input,
.lock-hover-wrap.is-locked select{
    background:var(--md-surface-container-low, #E7E0EC);
    filter:blur(0.3px);
    opacity:.75;
    cursor:not-allowed;
}
</style>

<script>
(function () {
  try {
    // ---- Resident search-select ----
    const residents = [
        @foreach ($residents as $resident)
        {
            id: {{ $resident->id }},
            label: {!! Js::from($resident->unit_number.' — '.$resident->name) !!},
            sub: {!! Js::from(($resident->id_number ? 'ID: '.$resident->id_number.' — ' : '').($resident->status !== 'active' ? 'Inactive' : 'Active')) !!},
            search: {!! Js::from(strtolower($resident->name.' '.$resident->unit_number.' '.$resident->id_number.' '.($resident->block_number ?? ''))) !!}
        },
        @endforeach
    ];

    const searchInput = document.getElementById('resident-search');
    const hiddenInput = document.getElementById('resident_id');
    const resultsBox = document.getElementById('resident-results');
    let activeIndex = -1;
    let currentList = [];

    // Positioned with position:fixed (not absolute) and recalculated here,
    // so the dropdown is never clipped when this form is opened inside the
    // scrollable popup modal — an absolutely-positioned box would get cut
    // off by the modal's own overflow:auto.
    function positionResultsBox() {
        const rect = searchInput.getBoundingClientRect();
        resultsBox.style.left = rect.left + 'px';
        resultsBox.style.top = (rect.bottom + 4) + 'px';
        resultsBox.style.width = rect.width + 'px';
    }

    function closeResults() {
        resultsBox.classList.remove('open');
        activeIndex = -1;
    }

    function highlight(index) {
        const opts = resultsBox.querySelectorAll('.opt');
        opts.forEach((el, i) => el.classList.toggle('active', i === index));
        if (opts[index]) opts[index].scrollIntoView({ block: 'nearest' });
        activeIndex = index;
    }

    function selectResident(r) {
        hiddenInput.value = r.id;
        searchInput.value = r.label;
        closeResults();
    }

    function renderResults(list) {
        currentList = list.slice(0, 25);
        resultsBox.innerHTML = '';
        if (!currentList.length) {
            resultsBox.innerHTML = '<div class="empty">No matching residents.</div>';
            positionResultsBox();
            resultsBox.classList.add('open');
            return;
        }
        currentList.forEach((r, i) => {
            const div = document.createElement('div');
            div.className = 'opt' + (currentList.length === 1 ? ' exact-match' : '');
            div.innerHTML = r.label + '<small>' + r.sub + '</small>';
            div.addEventListener('mousedown', (e) => {
                // mousedown (not click) fires before the input's blur,
                // so the selection registers before the list gets closed.
                e.preventDefault();
                selectResident(r);
            });
            resultsBox.appendChild(div);
        });
        positionResultsBox();
        resultsBox.classList.add('open');
        // Typing has narrowed it to exactly one resident — highlight it so
        // Enter picks it immediately, matching what the field is meant to do.
        highlight(currentList.length === 1 ? 0 : -1);
    }

    function runSearch() {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) { closeResults(); return; }
        renderResults(residents.filter(r => r.search.includes(q)));
    }

    searchInput.addEventListener('input', () => {
        hiddenInput.value = ''; // typing invalidates a prior selection
        runSearch();
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim()) runSearch();
    });

    searchInput.addEventListener('keydown', (e) => {
        if (!resultsBox.classList.contains('open') || !currentList.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlight((activeIndex + 1) % currentList.length);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlight((activeIndex - 1 + currentList.length) % currentList.length);
        } else if (e.key === 'Enter') {
            // Enter picks the highlighted row, or the sole remaining
            // match if typing has already narrowed it down to one.
            const index = activeIndex >= 0 ? activeIndex : (currentList.length === 1 ? 0 : -1);
            if (index >= 0) {
                e.preventDefault();
                selectResident(currentList[index]);
            }
        } else if (e.key === 'Escape') {
            closeResults();
        }
    });

    window.addEventListener('resize', () => { if (resultsBox.classList.contains('open')) positionResultsBox(); });
    document.addEventListener('scroll', () => { if (resultsBox.classList.contains('open')) positionResultsBox(); }, true);

    document.addEventListener('click', (e) => {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            closeResults();
        }
    });

    // Pre-fill search box label if a resident_id came back from old() (validation error redisplay)
    if (hiddenInput.value) {
        const match = residents.find(r => String(r.id) === String(hiddenInput.value));
        if (match) searchInput.value = match.label;
    }
  } catch (err) {
    console.error('Resident search failed to initialize:', err);
  }
})();
</script>

<script>
(function () {
  try {
    // ---- Fee <-> Fund are mutually exclusive; Fee locks the Amount ----
    // Kept in its own <script>/IIFE, isolated from the resident-search
    // block above: if that block ever throws, JS execution stops right
    // there and nothing after it in the same script would run — which
    // would silently disable this locking behavior. Splitting them means
    // a problem in one can never take out the other.
    const feeSelect = document.getElementById('fee_id');
    const fundSelect = document.getElementById('fund_id');
    const amountInput = document.getElementById('amount');
    const feeWrap = document.getElementById('fee-wrap');
    const fundWrap = document.getElementById('fund-wrap');
    const amountWrap = document.getElementById('amount-wrap');

    function applyLocks() {
        const feeOpt = feeSelect.options[feeSelect.selectedIndex];
        const feeAmount = feeOpt ? feeOpt.dataset.amount : null;
        const feeFundId = feeOpt ? feeOpt.dataset.fund : null;
        const feeChosen = !!feeSelect.value;
        const fundChosen = !!fundSelect.value;

        // Amount: locked only when a fee is chosen.
        if (feeChosen && feeAmount) {
            amountInput.value = feeAmount;
            amountInput.readOnly = true;
            amountWrap.classList.add('is-locked');
        } else {
            amountInput.readOnly = false;
            amountWrap.classList.remove('is-locked');
        }

        // Fee and Fund can't both be chosen — picking one locks the other.
        if (feeChosen) {
            if (feeFundId) fundSelect.value = feeFundId;
            fundSelect.disabled = true;
            fundWrap.classList.add('is-locked');
            feeSelect.disabled = false;
            feeWrap.classList.remove('is-locked');
        } else if (fundChosen) {
            feeSelect.disabled = true;
            feeWrap.classList.add('is-locked');
            fundSelect.disabled = false;
            fundWrap.classList.remove('is-locked');
        } else {
            feeSelect.disabled = false;
            fundSelect.disabled = false;
            feeWrap.classList.remove('is-locked');
            fundWrap.classList.remove('is-locked');
        }
    }

    feeSelect.addEventListener('change', () => {
        if (feeSelect.value) fundSelect.value = ''; // fee takes over the fund choice
        applyLocks();
    });
    fundSelect.addEventListener('change', () => {
        if (fundSelect.value) feeSelect.value = ''; // picking a fund directly clears any fee
        applyLocks();
    });

    applyLocks(); // handle old() redisplay after a validation error
  } catch (err) {
    console.error('Fee/Fund locking failed to initialize:', err);
  }
})();
</script>

@endsection
