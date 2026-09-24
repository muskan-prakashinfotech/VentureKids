<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assessment Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .page {
            padding: 28px;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 3px solid #EF7429;
        }
        .logo {
            height: 60px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            margin-top: 4px;
            color: #6b6b6b;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .card {
            border: 1px solid #f3c1a2;
            background: #fff7f2;
            padding: 12px;
            border-radius: 6px;
        }
        .overview {
            margin-bottom: 16px;
        }
        .overview-row {
            margin: 4px 0;
            line-height: 1.5;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 18px 0 10px;
            color: #EF7429;
            padding-bottom: 4px;
            border-bottom: 2px solid #EF7429;
        }
        .section-title.compact {
            margin-top: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #f3c1a2;
            padding: 8px;
            vertical-align: top;
            line-height: 1.5;
        }
        th {
            background: #EF7429;
            color: #fff;
            text-align: left;
        }
        tbody tr:nth-child(even) {
            background: #fffaf6;
        }
        .muted {
            color: #666;
            font-size: 11px;
        }
        .scenario-block {
            border: 1px solid #f3c1a2;
            border-left: 4px solid #EF7429;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff7f2;
        }
        .scenario-label {
            display: inline-block;
            background: #EF7429;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            margin-bottom: 6px;
        }
        .scenario-line {
            margin: 4px 0;
            line-height: 1.5;
        }
        .scenario-meta {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            gap: 8px;
            margin-bottom: 2px;
        }
        .scenario-subtitle {
            margin: 6px 0 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #6b6b6b;
        }
        .scenario-param {
            font-weight: bold;
        }
        .scenario-level {
            color: #EF7429;
            font-weight: bold;
            margin-left: 6px;
        }
        .scenario-evidence {
            display: block;
            margin-left: 0;
        }
        .scenario-level-badge {
            display: inline-block;
            background: #EF7429;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            margin-left: 0;
            white-space: nowrap;
        }
        .summary-text {
            white-space: pre-wrap;
            padding: 14px;
            border-radius: 6px;
            line-height: 1.7;
            font-size: 12.5px;
        }
        .graph-card {
            border: 1px solid #f3c1a2;
            background: #fffaf6;
            border-radius: 6px;
            padding: 12px;
            margin-top: 12px;
            page-break-before: always;
        }
        .graph-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #7b4b26;
            margin: 0 0 10px;
        }
        .graph-axis-note {
            font-size: 10px;
            color: #6b6b6b;
            margin-top: 8px;
            line-height: 1.5;
        }
        .graph-layout {
            width: 100%;
            background: #fffdfb;
            padding: 6px 0 0;
            box-sizing: border-box;
        }
        .graph-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .graph-grid td {
            border-top: 1px solid #ead7ca;
            border-bottom: 1px solid #ead7ca;
            border-left: none;
            border-right: none;
            padding: 0;
        }
        .graph-grid-score {
            width: 48px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #5b5b5b;
            height: 34px;
            border-right: 1px solid #ead7ca !important;
            background: #fffdfb;
        }
        .graph-grid-cell {
            height: 34px;
            background: #fffdfb;
        }
        .graph-grid-cell-inner {
            display: block;
            width: 56%;
            height: 34px;
            margin: 0 auto;
        }
        .graph-grid-fill {
            display: block;
            width: 100%;
            height: 34px;
            line-height: 34px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: transparent;
        }
        .graph-grid-label-row td {
            border: none;
            padding-top: 8px;
            vertical-align: top;
        }
        .graph-grid-label-spacer {
            width: 48px;
        }
        .graph-grid-parameter {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            text-align: center;
            line-height: 1.35;
            padding: 0 6px;
        }
        .graph-level-label {
            display: block;
            margin-top: 3px;
            font-size: 9px;
            color: #8a6b55;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <img src="{{ $schoolLogoPath ?? '' }}" alt="School Logo" class="logo">
            <p class="title">{{ $studentName }}</p>
            <p class="subtitle">REALQ Report</p>
        </div>

        <div class="overview card">
            <div class="overview-row">
                <strong>Overview:</strong>
                {{ $reportOverview ?? '' }}
            </div>
        </div>

        <p class="section-title">Performance Evaluation</p>
        <table>
            <thead>
                <tr>
                    <th style="width: 22%;">Parameter</th>
                    <th style="width: 18%;">Rubric Level</th>
                    <th>Evidence from Student Work</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($reportSections['performance_rows'] ?? []) as $row)
                    <tr>
                        <td>{{ $row['parameter'] ?? '' }}</td>
                        <td>{{ $row['level'] ?? '' }}</td>
                        <td>{{ $row['summary'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No performance data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @php
            $graphRows = collect($reportSections['performance_rows'] ?? [])
                ->filter(function ($row) {
                    return !empty($row['parameter']) && !empty($row['rubric_score']);
                })
                ->values();
            $graphRubrics = collect($reportSections['graph_rubrics'] ?? [])
                ->filter(function ($row) {
                    return !empty($row['name']) && !empty($row['score']);
                })
                ->sortBy('score')
                ->values();
            $maxGraphScore = $graphRubrics->max('score') ?: ($graphRows->max('rubric_score') ?: 0);
            $minGraphScore = $graphRubrics->min('score') ?: ($graphRows->min('rubric_score') ?: 1);
        @endphp

        @if($graphRows->isNotEmpty() && $maxGraphScore > 0)
            <div class="graph-card">
                <p class="graph-title">Baseline Assessment Overview</p>
                <div class="graph-layout">
                    <table class="graph-grid">
                        @for($score = $maxGraphScore; $score >= $minGraphScore; $score--)
                            <tr>
                                <td class="graph-grid-score">{{ $score }}</td>
                                @foreach($graphRows as $row)
                                    @php
                                        $rowScore = (int) ($row['rubric_score'] ?? 0);
                                        $isFilled = $rowScore >= $score;
                                        $isTop = $isFilled && $rowScore === $score;
                                    @endphp
                                    <td class="graph-grid-cell">
                                        <span class="graph-grid-cell-inner">
                                            @if($isFilled)
                                                <span class="graph-grid-fill" style="background-color: #ff8a1f;">
                                                    &nbsp;
                                                </span>
                                            @else
                                                <span class="graph-grid-fill">&nbsp;</span>
                                            @endif
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endfor
                        <tr class="graph-grid-label-row">
                            <td class="graph-grid-label-spacer"></td>
                            @foreach($graphRows as $row)
                                <td class="graph-grid-parameter">
                                    {{ $row['parameter'] ?? '' }}
                                    <span class="graph-level-label">{{ $row['level'] ?? '' }}</span>
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </div>
                <div class="graph-axis-note">
                    <strong>Performance Level:</strong>
                    @foreach($graphRubrics as $index => $rubric)
                        {{ (int) $rubric['score'] }} = {{ $rubric['name'] }}@if($index < ($graphRubrics->count() - 1)), @endif
                    @endforeach
                </div>
            </div>
        @endif

        <p class="section-title">Scenario-Based Assessments</p>
        @forelse(($reportSections['scenario_rows'] ?? []) as $row)
            <div class="scenario-block">
                <div class="scenario-label">{{ $row['label'] ?? 'Question' }}</div>
                @if(!empty($row['question']))
                    <div class="scenario-line">{{ $row['question'] }}</div>
                @endif
                <div class="scenario-subtitle">Response Analysis</div>
                @foreach(($row['evidence'] ?? []) as $line)
                    @php
                        $parts = explode(' - ', $line, 2);
                        $left = trim($parts[0] ?? '');
                        $evidenceText = trim($parts[1] ?? '');
                        $leftParts = explode(':', $left, 2);
                        $paramName = trim($leftParts[0] ?? '');
                        $levelName = trim($leftParts[1] ?? '');
                    @endphp
                    <div class="scenario-line">
                        <div class="scenario-meta">
                            @if($paramName !== '')
                                <span class="scenario-param">{{ $paramName }}</span>
                            @endif
                            @if($levelName !== '')
                                <span class="scenario-level-badge">{{ $levelName }}</span>
                            @endif
                        </div>
                        @if($evidenceText !== '')
                            <span class="scenario-evidence">{{ $evidenceText }}</span>
                        @else
                            <span class="scenario-evidence">{{ $line }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="overview card">
                <div class="overview-row">No scenario evidence available.</div>
            </div>
        @endforelse

        <p class="section-title compact">Summary</p>
        <div class="summary-text card">{{ $reportSections['summary_text'] ?? $reportBody }}</div>
    </div>
</body>
</html>
