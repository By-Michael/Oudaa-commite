@props([
    'title',             // e.g. "Residents"
    'noun',              // plural noun for the subtitle, e.g. "members", "payments"
    'shown',             // count of rows matching current filters (paginator total)
    'total',             // count of all rows regardless of filters
    'exportExcel',       // URL for the Excel export button
    'exportPdf',         // URL for the PDF export button
    'panelId',           // unique id for this page's filter panel, e.g. "filters-residents"
])
{{-- $icon is a named slot (<x-slot:icon>...</x-slot:icon>) holding raw inline <svg> markup --}}

<div class="list-header">
    <div class="list-header-top">
        <div class="list-header-icon">{!! $icon !!}</div>
        <div class="list-header-text">
            <h2>{{ $title }}</h2>
            <p>{{ number_format($shown) }} of {{ number_format($total) }} {{ $noun }} match the current filters</p>
        </div>
        <div class="list-header-actions">
            <a href="{{ $exportExcel }}" class="btn btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Excel
            </a>
            <a href="{{ $exportPdf }}" class="btn btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                PDF
            </a>
        </div>
    </div>

    <button type="button" class="btn btn-sm js-filter-toggle" data-target="#{{ $panelId }}" style="margin-top:14px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>

    <div id="{{ $panelId }}" class="filter-panel">
        {{ $slot }}
    </div>
</div>
