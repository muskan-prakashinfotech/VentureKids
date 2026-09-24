<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Session Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }
        .page {
            padding: 28px 30px;
        }

        /* -- Header -- */
        .header {
            border-bottom: 2px solid #e07b2a;
            padding-bottom: 10px;
            margin-bottom: 14px;
            width: 100%;
        }
        .header-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .header-inner td {
            padding: 0;
            vertical-align: middle;
        }
        .header-inner td.header-right {
            text-align: right;
            font-size: 9px;
            color: #666;
            white-space: nowrap;
        }
        .header-inner td.header-center {
            text-align: center;
        }
        .header-logo {
            height: 40px;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #e07b2a;
        }
        .report-title {
            font-size: 11px;
            color: #444;
            margin-top: 2px;
        }

        /* -- Date range badge -- */
        .date-range-bar {
            background: #fff4ec;
            border: 1px solid #f0c090;
            border-radius: 4px;
            padding: 5px 10px;
            margin-bottom: 14px;
            font-size: 10px;
            color: #333;
        }
        .date-range-bar strong { color: #e07b2a; }

        /* -- Main table -- */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .report-table th,
        .report-table td {
            border: 1px solid #ccc;
            padding: 8px 8px;
            vertical-align: top;
        }
        .report-table thead tr {
            background: #fef3d8;
        }
        .report-table th {
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }

        /* Column widths */
        .col-detail  { width: 16%; }
        .col-picture { width: 18%; }
        .col-summary { width: 32%; }
        .col-outcome { width: 17%; }
        .col-skill   { width: 17%; }

        .session-topic {
            font-weight: bold;
            font-size: 10px;
            color: #222;
            margin-bottom: 3px;
        }
        .session-date {
            font-size: 9px;
            color: #666;
        }
        .session-photo img {
            max-width: 100%;
            max-height: 130px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #ddd;
            display: block;
        }
        .cell-text {
            font-size: 10px;
            line-height: 1.5;
            color: #333;
        }
        .cell-muted {
            color: #aaa;
            font-size: 10px;
        }

        /* -- Footer -- */
        .footer {
            margin-top: 18px;
            border-top: 1px solid #eee;
            padding-top: 6px;
            font-size: 8px;
            color: #aaa;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <table class="header-inner">
            <tr>
                <td>
                    <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                    <div class="report-title">Session Report</div>
                </td>
                <td class="header-center">
                    @if($logoData)
                        <img src="{{ $logoData }}" alt="" class="header-logo">
                    @endif
                </td>
                <td class="header-right">Generated: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- Date range --}}
    @if($fromDate || $toDate)
    <div class="date-range-bar">
        <strong>Period:</strong>
        {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : 'Start' }}
        &nbsp;&mdash;&nbsp;
        {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d/m/Y') : 'End' }}
    </div>
    @endif

    {{-- Table --}}
    <table class="report-table">
        <thead>
            <tr>
                <th class="col-detail">Session Details</th>
                <th class="col-picture">Class in Action</th>
                <th class="col-summary">What was covered during the session</th>
                <th class="col-outcome">Learning Outcome</th>
                <th class="col-skill">Skill Focus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
            <tr>
                <td class="col-detail">
                    <div class="session-topic">{{ $row['session_title'] }}</div>
                    <div class="session-date">{{ $row['date_formatted'] }}</div>
                </td>
                <td class="col-picture">
                    @if($row['photo'])
                    <div class="session-photo">
                        <img src="{{ $row['photo'] }}" alt="Session Photo">
                    </div>
                    @else
                    <span class="cell-muted">No photo</span>
                    @endif
                </td>
                <td class="col-summary">
                    @if($row['session_summary'])
                    <div class="cell-text">{{ $row['session_summary'] }}</div>
                    @else
                    <span class="cell-muted">—</span>
                    @endif
                </td>
                <td class="col-outcome">
                    @if(!empty($row['learning_outcome']))
                    <div class="cell-text">{{ $row['learning_outcome'] }}</div>
                    @else
                    <span class="cell-muted">—</span>
                    @endif
                </td>
                <td class="col-skill">
                    @if(!empty($row['skill_focus']))
                    <div class="cell-text">{{ $row['skill_focus'] }}</div>
                    @else
                    <span class="cell-muted">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#888; padding:20px;">
                    No session reports found for the selected period.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
