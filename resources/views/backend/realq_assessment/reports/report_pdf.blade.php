<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>REALQ School Report</title>
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
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 14px 0 8px;
            color: #EF7429;
            padding-bottom: 2px;
            border-bottom: 1px solid #EF7429;
        }
        .section-block { margin-bottom: 12px; }
        .section-body { line-height: 1.35; margin: 0; }
        .section-body ul, .section-body ol { margin: 0; padding-left: 16px; }
        .section-body li { margin: 0; line-height: 1.15; }
        .swp-title { font-weight: bold; margin: 4px 0 4px; }
        .swp-list { margin: 0 0 4px; padding-left: 16px; }
        .swp-list li { margin: 0; padding: 0; line-height: 1.4; }
        .swp-divider { border: 0; border-top: 1px solid #cfcfcf; margin: 10px 0; }
        .swp-insight { margin: 6px 0 0; color: #EF7429; font-weight: bold; font-size: 11px; }
        .overview-label { font-weight: bold; margin: 0 0 4px; }
        .overview-item { margin: 0 0 3px; line-height: 1.3; }
        .swp-chart-wrap { margin: 10px 0 8px; }
        .swp-chart-title { text-align: center; font-size: 11px; font-weight: bold; margin: 0 0 6px; color: #7b4b26; }
        .swp-chart-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .swp-chart-grid td { padding: 0; border: none; }
        .swp-chart-score {
            width: 46px;
            height: 28px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #5b5b5b;
            border-right: 1px solid #ead7ca !important;
            border-bottom: 1px solid #ead7ca !important;
            background: #fff;
        }
        .swp-chart-cell {
            height: 28px;
            border-bottom: 1px solid #ead7ca !important;
            background: #fff;
        }
        .swp-chart-cell-inner {
            display: block;
            width: 52%;
            height: 28px;
            margin: 0 auto;
        }
        .swp-chart-fill {
            display: block;
            width: 100%;
            height: 28px;
            background: #EF7429;
            color: transparent;
        }
        .swp-chart-fill.is-top {
            color: transparent;
        }
        .swp-chart-label-row td {
            padding-top: 8px;
            vertical-align: top;
            border: none !important;
        }
        .swp-chart-label-spacer { width: 46px; }
        .swp-chart-label {
            font-size: 10px;
            text-align: center;
            line-height: 1.3;
            color: #333;
            padding: 0 4px;
        }
        .swp-chart-empty {
            display: block;
            width: 100%;
            height: 28px;
            line-height: 28px;
            background: transparent;
        }
        .consolidated-chart-wrap { margin: 20px 0 6px; }
        .consolidated-chart-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #7b4b26;
            margin: 0 0 10px;
            padding: 6px 10px;
            border: 1px solid #f3c1a2;
            background: #fff7f2;
            border-radius: 4px;
        }
        .consolidated-chart-legend {
            text-align: right;
            margin: 0 0 8px;
            font-size: 9px;
            color: #555;
        }
        .consolidated-legend-item {
            display: inline-block;
            margin-left: 10px;
            white-space: nowrap;
        }
        .consolidated-legend-swatch {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 4px;
            vertical-align: middle;
        }
        .consolidated-chart-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .consolidated-chart-grid td {
            padding: 0;
            border: none;
        }
        .consolidated-chart-score {
            width: 46px;
            height: 28px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #5b5b5b;
            border-right: 1px solid #ead7ca !important;
            border-bottom: 1px solid #ead7ca !important;
            background: #fff;
        }
        .consolidated-chart-cell {
            height: 28px;
            border-bottom: 1px solid #ead7ca !important;
            background: #fff;
        }
        .consolidated-chart-cell.group-end {
            border-right: 10px solid #fff !important;
        }
        .consolidated-chart-cell-inner {
            display: block;
            width: 100%;
            height: 28px;
            margin: 0;
        }
        .consolidated-chart-fill {
            display: block;
            width: 100%;
            height: 28px;
        }
        .consolidated-chart-empty {
            display: block;
            width: 100%;
            height: 28px;
            background: transparent;
        }
        .consolidated-chart-label-row td {
            padding-top: 8px;
            border: none !important;
            vertical-align: top;
        }
        .consolidated-chart-label-spacer {
            width: 46px;
        }
        .consolidated-chart-parameter {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.3;
            color: #333;
            padding: 0 6px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        @if(!empty($schoolLogoPath))
            <img src="{{ $schoolLogoPath }}" alt="School Logo" class="logo">
        @endif
        <p class="title">{{ $schoolName }}</p>
        <p class="subtitle">REALQ Report</p>
    </div>

    <div class="section-block">
        <p class="section-title">Assessment Overview</p>
        <div class="section-body">
            <div class="overview-label">Grades Involved:</div>
            @foreach(($gradeSummary ?? []) as $grade)
                <div class="overview-item">{{ $grade['grade_name'] ?? '' }}: {{ $grade['total_students'] ?? 0 }} students</div>
            @endforeach
            <div class="overview-label" style="margin-top:6px;">Assessment Format:</div>
            <div class="overview-item">{{ $assessmentFormat ?? '' }}</div>
        </div>
    </div>

    <div class="section-block">
        <p class="section-title">Assessment Focus</p>
        <div class="section-body">{{ $reportSections['assessment_overview'] ?? '' }}</div>
    </div>

    <div class="section-block">
        <p class="section-title">School-Wide Performance</p>
        <div class="section-body">

        @php
            $paramLevelsMap        = [];
            $allRubricNames        = [];
            $aggregatedParamCounts = [];
            $aggregatedParamTotals = [];
            $paramNamesOrdered     = []; // canonical display name, keyed by lowercase

            foreach (($gradeSummary ?? []) as $grade) {
                $gradeTotalStudents = (int) ($grade['total_students'] ?? 0);
                foreach (($grade['parameters'] ?? []) as $param) {
                    $pName = trim($param['name'] ?? '');
                    $key   = strtolower($pName);
                    if ($pName !== '' && !isset($paramNamesOrdered[$key])) {
                        $paramNamesOrdered[$key] = $pName;
                    }
                    if (!isset($paramLevelsMap[$key])) {
                        $paramLevelsMap[$key] = $param['levels'] ?? [];
                    }
                    $aggregatedParamTotals[$key] = ($aggregatedParamTotals[$key] ?? 0) + $gradeTotalStudents;
                    foreach (($param['levels'] ?? []) as $levelRow) {
                        $rubricName = trim((string) ($levelRow['name'] ?? ''));
                        if ($rubricName !== '') {
                            $allRubricNames[strtolower($rubricName)] = $rubricName;
                            $aggregatedParamCounts[$key][strtolower($rubricName)] =
                                ($aggregatedParamCounts[$key][strtolower($rubricName)] ?? 0) + (int) ($levelRow['count'] ?? 0);
                        }
                    }
                }
            }

            $aggregatedParamLevelsMap = [];
            foreach ($aggregatedParamCounts as $paramKey => $rubricCounts) {
                $paramTotalStudents = (int) ($aggregatedParamTotals[$paramKey] ?? 0);
                $levels = [];
                foreach ($allRubricNames as $rubricKey => $rubricName) {
                    $count    = (int) ($rubricCounts[$rubricKey] ?? 0);
                    $levels[] = [
                        'name'    => $rubricName,
                        'count'   => $count,
                        'percent' => $paramTotalStudents > 0 ? (int) round(($count / $paramTotalStudents) * 100) : 0,
                    ];
                }
                $aggregatedParamLevelsMap[$paramKey] = $levels;
            }

            // Build items from structured data — avoids OpenAI text formatting variations
            $items = [];
            foreach ($paramNamesOrdered as $pKey => $pName) {
                $levels  = $aggregatedParamLevelsMap[$pKey] ?? [];
                $bullets = [];
                foreach ($levels as $levelRow) {
                    $bullets[] = ($levelRow['name'] ?? '') . ': ' . ($levelRow['percent'] ?? 0) . '%';
                }
                $items[] = ['title' => $pName, 'bullets' => $bullets, 'insight' => ''];
            }

            // Extract per-parameter insights from OpenAI text (best-effort)
            $swpRaw = (string) ($reportSections['school_wide_parameter_snapshot'] ?? '');
            if ($swpRaw !== '') {
                $insightMap = [];
                foreach ($paramNamesOrdered as $pKey => $pName) {
                    $pattern = '/' . preg_quote($pName, '/') . '.*?(?:Insight|Interpretation)\s*:\s*([^\n]+)/is';
                    if (preg_match($pattern, $swpRaw, $m)) {
                        $insightMap[$pKey] = trim($m[1]);
                    }
                }
                foreach ($items as &$item) {
                    $pKey = strtolower(trim($item['title']));
                    if (!empty($insightMap[$pKey])) {
                        $item['insight'] = $insightMap[$pKey];
                    }
                }
                unset($item);
            }

            $consolidatedParamTitles = array_values($paramNamesOrdered);

            $consolidatedRubrics  = array_values($allRubricNames);
            $consolidatedColors   = ['#2F80C1', '#FF8A1F', '#37A83A', '#E53935', '#7E57C2', '#26A69A'];
            $consolidatedColorMap = [];
            foreach ($consolidatedRubrics as $index => $rubricName) {
                $consolidatedColorMap[$rubricName] = $consolidatedColors[$index % count($consolidatedColors)];
            }

            $consolidatedMaxPercent = 0;
            foreach ($consolidatedParamTitles as $paramTitle) {
                $paramKey = strtolower(trim($paramTitle));
                foreach (($aggregatedParamLevelsMap[$paramKey] ?? []) as $levelRow) {
                    $consolidatedMaxPercent = max($consolidatedMaxPercent, (int) ($levelRow['percent'] ?? 0));
                }
            }
            $consolidatedTop  = max(5, (int) (ceil($consolidatedMaxPercent / 5) * 5));
            $consolidatedStep = max(5, (int) ceil($consolidatedTop / 4));
            $consolidatedTicks = [
                $consolidatedTop,
                max($consolidatedTop - $consolidatedStep, 0),
                max($consolidatedTop - (2 * $consolidatedStep), 0),
                max($consolidatedTop - (3 * $consolidatedStep), 0),
            ];
        @endphp

        @php $itemCount = count($items); @endphp

        @foreach($items as $idx => $item)

            <div class="swp-title" style="margin-top:4px;">{{ $idx + 1 }}. {{ $item['title'] }}</div>

            @if(!empty($item['bullets']))
                <ul class="swp-list">
                    @foreach($item['bullets'] as $bullet)
                        <li>{{ $bullet }}</li>
                    @endforeach
                </ul>
            @endif

            @php
                $paramKey = strtolower(trim($item['title']));
                $chartLevels = $aggregatedParamLevelsMap[$paramKey] ?? [];
                $maxPercent = 0;
                foreach ($chartLevels as $levelRow) {
                    $maxPercent = max($maxPercent, (int) ($levelRow['percent'] ?? 0));
                }
                $chartTop = max(5, (int) (ceil($maxPercent / 5) * 5));
                $chartStep = max(5, (int) ceil($chartTop / 4));
                $chartTicks = [$chartTop, max($chartTop - $chartStep, 0), max($chartTop - (2 * $chartStep), 0), max($chartTop - (3 * $chartStep), 0)];
            @endphp

            @if(!empty($chartLevels))
                <div class="swp-chart-wrap">
                    <div class="swp-chart-title">{{ $item['title'] }} Distribution</div>
                    <table class="swp-chart-grid">
                        @foreach($chartTicks as $tick)
                            <tr>
                                <td class="swp-chart-score">{{ $tick }}</td>
                                @foreach($chartLevels as $levelRow)
                                    @php
                                        $percent = (int) ($levelRow['percent'] ?? 0);
                                        $isFilled = $percent >= $tick && $tick > 0;
                                        $isTop = $isFilled && $percent < ($tick + $chartStep);
                                    @endphp
                                    <td class="swp-chart-cell">
                                        <span class="swp-chart-cell-inner">
                                            @if($isFilled)
                                                <span class="swp-chart-fill{{ $isTop ? ' is-top' : '' }}">
                                                    &nbsp;
                                                </span>
                                            @else
                                                <span class="swp-chart-empty">&nbsp;</span>
                                            @endif
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="swp-chart-label-row">
                            <td class="swp-chart-label-spacer"></td>
                            @foreach($chartLevels as $levelRow)
                                <td class="swp-chart-label">{{ $levelRow['name'] ?? '' }}</td>
                            @endforeach
                        </tr>
                    </table>
                </div>
            @endif

            @if(!empty($item['insight']))
                <p class="swp-insight">Insight: {{ $item['insight'] }}</p>
            @endif

            @if($idx < $itemCount - 1)
                <hr class="swp-divider">
            @endif

        @endforeach

        @if(!empty($consolidatedParamTitles) && !empty($consolidatedRubrics))
            <div class="consolidated-chart-wrap">
                <div class="consolidated-chart-title">Consolidated REALQ Assessment Overview</div>
                <div class="consolidated-chart-legend">
                    @foreach($consolidatedRubrics as $rubricName)
                        <span class="consolidated-legend-item">
                            <span class="consolidated-legend-swatch" style="background: {{ $consolidatedColorMap[$rubricName] ?? '#EF7429' }};"></span>
                            {{ $rubricName }}
                        </span>
                    @endforeach
                </div>
                <table class="consolidated-chart-grid">
                    @foreach($consolidatedTicks as $tick)
                        <tr>
                            <td class="consolidated-chart-score">{{ $tick }}</td>
                            @foreach($consolidatedParamTitles as $paramTitle)
                                @php
                                    $paramKey = strtolower(trim($paramTitle));
                                    $levelRows = collect($aggregatedParamLevelsMap[$paramKey] ?? [])->keyBy(function ($row) {
                                        return strtolower(trim((string) ($row['name'] ?? '')));
                                    });
                                    $lastRubricIndex = count($consolidatedRubrics) - 1;
                                @endphp
                                @foreach($consolidatedRubrics as $rubricIndex => $rubricName)
                                    @php
                                        $levelRow = $levelRows->get(strtolower($rubricName), []);
                                        $percent = (int) ($levelRow['percent'] ?? 0);
                                        $isFilled = $percent >= $tick && $tick > 0;
                                        $isGroupEnd = $rubricIndex === $lastRubricIndex;
                                    @endphp
                                    <td class="consolidated-chart-cell{{ $isGroupEnd ? ' group-end' : '' }}">
                                        <span class="consolidated-chart-cell-inner">
                                            @if($isFilled)
                                                <span class="consolidated-chart-fill" style="background: {{ $consolidatedColorMap[$rubricName] ?? '#EF7429' }};"></span>
                                            @else
                                                <span class="consolidated-chart-empty">&nbsp;</span>
                                            @endif
                                        </span>
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="consolidated-chart-label-row">
                        <td class="consolidated-chart-label-spacer"></td>
                        @foreach($consolidatedParamTitles as $paramTitle)
                            <td class="consolidated-chart-parameter" colspan="{{ count($consolidatedRubrics) }}">{{ $paramTitle }}</td>
                        @endforeach
                    </tr>
                </table>
            </div>
        @endif

        </div>
    </div>

    <div class="section-block">
        <p class="section-title">Cross-Parameter Patterns</p>
        <div class="section-body">{!! nl2br(e($reportSections['cross_parameter_patterns'] ?? '')) !!}</div>
    </div>

    <div class="section-block">
        <p class="section-title">Implications for the School</p>
        <div class="section-body">{!! nl2br(e($reportSections['what_this_means'] ?? '')) !!}</div>
    </div>

    <div class="section-block">
        <p class="section-title">Actionable Recommendations</p>
        <div class="section-body">{!! nl2br(e($reportSections['actionable_recommendations'] ?? '')) !!}</div>
    </div>

    <div class="section-block">
        <div class="section-body">{!! nl2br(e($reportSections['moderation_note'] ?? '')) !!}</div>
    </div>

</div>
</body>
</html>

