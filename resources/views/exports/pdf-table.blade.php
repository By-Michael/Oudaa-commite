<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1F2933; font-size: 11px; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #14919B; }
        .meta { color: #667085; font-size: 10px; margin-bottom: 2px; }
        .filters { margin: 10px 0 14px; }
        .filters span {
            display: inline-block; background: #F4EEFB; color: #5B4488; border-radius: 4px;
            padding: 3px 8px; margin: 0 6px 6px 0; font-size: 9.5px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead th {
            background: #14919B; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px;
        }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #E2E8F0; font-size: 10px; }
        tbody tr:nth-child(even) { background: #F8FAFC; }
        .empty { padding: 16px 0; color: #667085; font-style: italic; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 9px; color: #98A2B3; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Generated {{ $generatedAt->format('Y-m-d H:i') }}</div>

    @if (!empty($appliedFilters))
        <div class="filters">
            @foreach ($appliedFilters as $line)
                <span>{{ $line }}</span>
            @endforeach
        </div>
    @endif

    @php($rows = is_array($rows) ? $rows : iterator_to_array($rows))

    @if (empty($rows))
        <div class="empty">No records match the selected filters.</div>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="meta" style="margin-top:10px;">{{ count($rows) }} record{{ count($rows) === 1 ? '' : 's' }}</div>
    @endif

    <div class="footer">Oudaa Committee Panel — Report generated {{ $generatedAt->format('Y-m-d H:i') }}</div>
</body>
</html>
