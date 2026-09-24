@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Student Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.realqassessment.index') }}">RealQ Assessment</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.realqassessment.student-reports.index') }}">Student Reports</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Student info bar --}}
            <div class="card mb-3">
                <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap" style="gap:20px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap:20px;">
                        <div><strong>School:</strong> {{ optional(optional($student)->school)->school_name ?? '—' }}</div>
                        <div><strong>Student:</strong> {{ optional($student)->name ?? '—' }}</div>
                        <div><strong>Status:</strong>
                            @if($reportRow->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($reportRow->status === 'generated')
                                <span class="badge badge-info">Generated</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </div>
                        @if($reportRow->approved_at)
                            <div><strong>Approved at:</strong> {{ \Carbon\Carbon::parse($reportRow->approved_at)->format('d M Y, h:i A') }}</div>
                        @endif
                    </div>
                    <a href="{{ route('backend.realqassessment.student-reports.index') }}" class="btn btn-warning btn-sm ml-auto">
                        <i class="fas fa-chevron-left"></i> Back
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('backend.realqassessment.student-reports.update', $reportRow->id) }}">
                @csrf
                @method('PUT')

                {{-- ── Overview ──────────────────────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header"><h5 class="mb-0">Overview</h5></div>
                    <div class="card-body">
                        <textarea name="overview" class="form-control" rows="4"
                                  placeholder="Report overview paragraph…">{{ old('overview', $reportData['overview'] ?? '') }}</textarea>
                    </div>
                </div>

                {{-- ── Performance Table ─────────────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header"><h5 class="mb-0">Performance Table</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:200px;">Parameter</th>
                                    <th style="width:200px;">Rubric Level</th>
                                    <th>Evidence / Summary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['performance_rows'] ?? [] as $pIdx => $pRow)
                                    <tr>
                                        <td class="align-middle">
                                            <strong>{{ $pRow['parameter'] }}</strong>
                                            <input type="hidden"
                                                   name="performance_rows[{{ $pIdx }}][parameter]"
                                                   value="{{ $pRow['parameter'] }}">
                                        </td>
                                        <td class="align-middle perf-rubric-cell" data-param="{{ $pRow['parameter'] }}">
                                            <select class="form-control form-control-sm perf-rubric-display" disabled>
                                                <option value="">— select —</option>
                                                @foreach($rubrics as $rubric)
                                                    <option value="{{ $rubric->id }}"
                                                        {{ (int)($pRow['rubric_id'] ?? 0) === (int)$rubric->id ? 'selected' : '' }}>
                                                        {{ $rubric->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden"
                                                   name="performance_rows[{{ $pIdx }}][rubric_id]"
                                                   class="perf-rubric-id"
                                                   value="{{ $pRow['rubric_id'] ?? '' }}">
                                            <input type="hidden"
                                                   name="performance_rows[{{ $pIdx }}][level]"
                                                   class="perf-level-input"
                                                   value="{{ $pRow['level'] ?? '' }}">
                                        </td>
                                        <td>
                                            <textarea name="performance_rows[{{ $pIdx }}][evidence]"
                                                      class="form-control form-control-sm"
                                                      rows="3">{{ $pRow['evidence'] ?? '' }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── Scenario Questions ────────────────────────────────── --}}
                @foreach($reportData['scenario_rows'] ?? [] as $sIdx => $sRow)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                {{ $sRow['label'] ?? ('Question ' . ($sIdx + 1)) }}
                                @if(!empty($sRow['question']))
                                    <small class="text-muted ml-2">— {{ $sRow['question'] }}</small>
                                @endif
                            </h6>
                        </div>
                        {{-- Student's saved answer for this question --}}
                        @php $studentAnswer = $studentAnswers[$sIdx] ?? null; @endphp
                        @if($studentAnswer && trim($studentAnswer->answer_text ?? '') !== '')
                            <div class="px-3 pt-3 pb-2" style="border-bottom:1px solid #dee2e6;background:#fafafa;">
                                <p class="mb-1" style="font-size:12px;font-weight:600;color:#555;text-transform:uppercase;letter-spacing:0.4px;">Student's Answer</p>
                                <div style="white-space:pre-wrap;font-size:13px;line-height:1.7;color:#333;padding:10px 12px;background:#fff;border:1px solid #e3e6ea;border-radius:4px;word-break:break-word;">{{ $studentAnswer->answer_text }}</div>
                            </div>
                        @endif
                        <div class="card-body p-0">
                            <input type="hidden" name="scenario_rows[{{ $sIdx }}][label]"    value="{{ $sRow['label'] ?? '' }}">
                            <input type="hidden" name="scenario_rows[{{ $sIdx }}][question]" value="{{ $sRow['question'] ?? '' }}">
                            <table class="table table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:200px;">Parameter</th>
                                        <th style="width:200px;">Rubric Level</th>
                                        <th>Evidence Text</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sRow['evidence'] ?? [] as $eIdx => $eItem)
                                        <tr>
                                            <td class="align-middle">
                                                <strong>{{ $eItem['parameter'] ?? '' }}</strong>
                                                <input type="hidden"
                                                       name="scenario_rows[{{ $sIdx }}][evidence][{{ $eIdx }}][parameter]"
                                                       value="{{ $eItem['parameter'] ?? '' }}">
                                            </td>
                                            <td class="align-middle">
                                                <select name="scenario_rows[{{ $sIdx }}][evidence][{{ $eIdx }}][rubric_id]"
                                                        class="form-control form-control-sm scen-rubric-select"
                                                        data-s="{{ $sIdx }}" data-e="{{ $eIdx }}">
                                                    <option value="">— select —</option>
                                                    @foreach($rubrics as $rubric)
                                                        <option value="{{ $rubric->id }}"
                                                            data-name="{{ $rubric->name }}"
                                                            data-score="{{ $rubric->score }}"
                                                            {{ (int)($eItem['rubric_id'] ?? 0) === (int)$rubric->id ? 'selected' : '' }}>
                                                            {{ $rubric->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden"
                                                       name="scenario_rows[{{ $sIdx }}][evidence][{{ $eIdx }}][level]"
                                                       class="scen-level-input"
                                                       value="{{ $eItem['level'] ?? '' }}">
                                            </td>
                                            <td>
                                                <textarea name="scenario_rows[{{ $sIdx }}][evidence][{{ $eIdx }}][text]"
                                                          class="form-control form-control-sm"
                                                          rows="3">{{ $eItem['text'] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                {{-- ── Summary ───────────────────────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header"><h5 class="mb-0">Narrative Summary</h5></div>
                    <div class="card-body">
                        <textarea name="summary" class="form-control" rows="6"
                                  placeholder="Overall narrative summary for the student…">{{ old('summary', $reportData['summary'] ?? '') }}</textarea>
                    </div>
                </div>

                {{-- ── Action buttons ────────────────────────────────────── --}}
                {{-- ── Action buttons ────────────────────────────────────── --}}
                @if($reportRow->status !== 'approved')
                    <div class="text-right" style="margin-bottom:40px;">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                @else
                    <div class="mb-4 text-right">
                        <span class="text-success font-weight-bold">
                            <i class="fas fa-check-circle"></i> This report has been approved and published to the student.
                        </span>
                    </div>
                @endif

            </form>

        </div>
    </section>
</div>

<script type="application/json" id="rubric-defs-json">@json($rubrics->map(fn($r) => ['id' => (string) $r->id, 'score' => (int) $r->score, 'name' => $r->name])->values())</script>

<script>
$(document).ready(function () {

    // Rubric definitions from PHP: [{id, score, name}, ...] ordered by score asc
    const rubricDefs = JSON.parse(document.getElementById('rubric-defs-json').textContent || '[]');

    // Find rubric whose score is closest to avgScore (mirrors PHP findRubricByAverageScore)
    function findClosestRubric(avgScore) {
        let best = null, minGap = Infinity;
        rubricDefs.forEach(function (r) {
            const gap = Math.abs(r.score - avgScore);
            if (gap < minGap) { minGap = gap; best = r; }
        });
        return best;
    }

    // Recalculate performance table rubric levels from current scenario selections
    function recalcPerformanceRubrics() {
        // Collect scores per parameter across all scenario evidence rows
        const paramScores = {};
        $('.scen-rubric-select').each(function () {
            const $tr    = $(this).closest('tr');
            const param  = ($tr.find('input[type="hidden"][name$="[parameter]"]').val() || '').trim().toLowerCase();
            const selOpt = $(this).find('option:selected');
            const score  = parseInt(selOpt.data('score'));
            if (param && $(this).val() && !isNaN(score)) {
                if (!paramScores[param]) paramScores[param] = [];
                paramScores[param].push(score);
            }
        });

        // Update each performance row with the averaged rubric
        $('.perf-rubric-cell').each(function () {
            const param  = (($(this).data('param') || '') + '').trim().toLowerCase();
            const scores = paramScores[param];
            if (!scores || scores.length === 0) return;

            const avg     = Math.round(scores.reduce(function (a, b) { return a + b; }, 0) / scores.length);
            const closest = findClosestRubric(avg);
            if (!closest) return;

            $(this).find('.perf-rubric-display').val(closest.id);
            $(this).find('.perf-rubric-id').val(closest.id);
            $(this).find('.perf-level-input').val(closest.name);
        });
    }

    // Sync scenario level name into hidden input and trigger performance recalc
    $(document).on('change', '.scen-rubric-select', function () {
        const selected = $(this).find('option:selected');
        $(this).closest('tr').find('.scen-level-input').val(selected.data('name') || '');
        recalcPerformanceRubrics();
    });

    // Initialize performance table on page load from existing scenario selections
    recalcPerformanceRubrics();

});
</script>
@endsection
