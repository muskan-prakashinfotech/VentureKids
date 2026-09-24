<?php

namespace App\Traits;

use App\Models\AssessmentStudentReport;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\RealQStudentParameterScore;


trait RealQReportParser
{
    protected function parseSubjectiveReportSections(string $text, array $questionTexts = [], ?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        $sections = [
            'section_a'        => '',
            'section_b'        => '',
            'section_c'        => '',
            'performance_rows' => [],
            'scenario_rows'    => [],
            'summary_text'     => '',
            'graph_rubrics'    => [],
        ];

        $clean = trim($text);
        if ($clean === '') {
            return $sections;
        }

        $json = json_decode($clean, true);
        if (!is_array($json)) {
            $jsonCandidate = $this->extractJsonBlock($clean);
            if ($jsonCandidate !== '') {
                $json = json_decode($jsonCandidate, true);
            }
        }

        if (is_array($json) && isset($json['Section A'], $json['Section B'], $json['Section C'])) {
            $sections['section_a']        = is_array($json['Section A']) ? json_encode($json['Section A'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $sections['section_b']        = is_array($json['Section B']) ? json_encode($json['Section B'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $sections['section_c']        = is_string($json['Section C']) ? trim($json['Section C']) : '';
            $sections['scenario_rows']    = $this->parseSectionAJson($json['Section A'], $questionTexts);
            $sections['performance_rows'] = $this->buildPerformanceRowsFromSectionAJson($json['Section A'], $json['Section B'], $assignmentRow);
            $sections['graph_rubrics']    = $this->getRubricDefinitionsForAssignment($assignmentRow)['ordered'] ?? [];
            if (empty($sections['performance_rows'])) {
                $sections['performance_rows'] = $this->parseSectionBJson($json['Section B']);
            }
            $sections['summary_text'] = is_string($json['Section C']) ? trim($json['Section C']) : '';
            return $sections;
        }

        $positions = [];
        $labels = [
            'a' => 'Section A: Per-Question Evidence',
            'b' => 'Section B: Aggregated Parameter Profile',
            'c' => 'Section C: Narrative Report',
        ];
        foreach ($labels as $key => $label) {
            $pos = stripos($clean, $label);
            if ($pos !== false) {
                $positions[$key] = $pos;
            }
        }

        if (isset($positions['a'])) {
            $end = $positions['b'] ?? $positions['c'] ?? strlen($clean);
            $sections['section_a'] = trim(substr($clean, $positions['a'], $end - $positions['a']));
        }
        if (isset($positions['b'])) {
            $end = $positions['c'] ?? strlen($clean);
            $sections['section_b'] = trim(substr($clean, $positions['b'], $end - $positions['b']));
        }
        if (isset($positions['c'])) {
            $sections['section_c'] = trim(substr($clean, $positions['c']));
        }

        $sections['scenario_rows']    = $this->parseSectionAQuestions($sections['section_a']);
        $sections['performance_rows'] = $this->buildPerformanceRowsFromScenarioRows(
            $sections['scenario_rows'],
            $this->parseSectionBParameters($sections['section_b']),
            $assignmentRow
        );
        $sections['graph_rubrics'] = $this->getRubricDefinitionsForAssignment($assignmentRow)['ordered'] ?? [];
        if (empty($sections['performance_rows'])) {
            $sections['performance_rows'] = $this->parseSectionBParameters($sections['section_b']);
        }
        $sections['summary_text'] = $this->parseSectionCSummary($sections['section_c']);

        return $sections;
    }

    /**
     * Build the structured report_data JSON stored in assessment_student_report.report_data.
     * This is the format the admin edits and that regenerates the PDF.
     *
     * scenario_rows here keep full { parameter, level, rubric_id, text } per evidence item,
     * unlike parseSectionAJson which collapses level info.
     */
    protected function buildReportDataJson(
        string $rawAiResponse,
        array $reportSections,
        array $questionTexts,
        ?RealQAssessmentSchoolAssignment $assignmentRow,
        string $overview
    ): array {
        $rubricDefs = $this->getRubricDefinitionsForAssignment($assignmentRow);

        // Build structured scenario rows from raw AI JSON (preserves level per evidence item)
        $structuredScenarioRows = [];
        $rawJson = json_decode($rawAiResponse, true);
        if (!is_array($rawJson)) {
            $candidate = $this->extractJsonBlock($rawAiResponse);
            if ($candidate !== '') {
                $rawJson = json_decode($candidate, true);
            }
        }

        if (is_array($rawJson) && isset($rawJson['Section A']) && is_array($rawJson['Section A'])) {
            foreach ($rawJson['Section A'] as $questionLabel => $params) {
                if (!is_array($params)) {
                    continue;
                }
                $evidenceItems = [];
                foreach ($params as $paramKey => $evidenceText) {

                    $parts       = explode(' - ', (string) $paramKey, 2);
                    $paramName   = trim($parts[0]);
                    $keyPart2    = trim($parts[1] ?? '');   // level name OR parameter description
                    $evidenceStr = trim((string) $evidenceText);
                    $rubric      = null;
                    $levelName   = '';

                    // Try resolving key part 2 as a rubric level (covers Format A)
                    if ($keyPart2 !== '') {
                        $rubric = $rubricDefs['by_name'][$this->normalizeLabel($keyPart2)]
                            ?? $this->resolveRubricFromEvidenceValue($keyPart2, $rubricDefs);
                    }

                    if ($rubric) {
                        // Format A — level was in the key; value is clean evidence text
                        $levelName = $rubric['name'];
                    } else {
                        if ($evidenceStr !== '') {
                            $evParts       = explode(' - ', $evidenceStr, 2);
                            $possibleLevel = trim($evParts[0]);
                            $resolvedLevel = $rubricDefs['by_name'][$this->normalizeLabel($possibleLevel)]
                                ?? $this->resolveRubricFromEvidenceValue($possibleLevel, $rubricDefs);
                            if ($resolvedLevel) {
                                $rubric      = $resolvedLevel;
                                $levelName   = $rubric['name'];
                                // Strip "Level - " prefix so it doesn't appear in evidence text
                                $evidenceStr = isset($evParts[1]) ? trim($evParts[1]) : $evidenceStr;
                            }
                        }
                    }

                    $evidenceItems[] = [
                        'parameter' => $this->stripParameterDescription($paramName),
                        'level'     => $levelName,
                        'rubric_id' => $rubric ? (int) $rubric['id'] : null,
                        'text'      => $evidenceStr,
                    ];
                }
                $structuredScenarioRows[] = [
                    'label'    => (string) $questionLabel,
                    'question' => $questionTexts[$questionLabel] ?? '',
                    'evidence' => $evidenceItems,
                ];
            }
        }

        // Fall back to the simpler scenario_rows if the structured build is empty
        if (empty($structuredScenarioRows)) {
            foreach ($reportSections['scenario_rows'] ?? [] as $row) {
                $evidenceItems = [];
                foreach ($row['evidence'] ?? [] as $line) {
                    $parts     = explode(':', (string) $line, 2);
                    $paramName = trim($parts[0] ?? $line);
                    $text      = trim($parts[1] ?? '');
                    $evidenceItems[] = [
                        'parameter' => $paramName,
                        'level'     => '',
                        'rubric_id' => null,
                        'text'      => $text ?: (string) $line,
                    ];
                }
                $structuredScenarioRows[] = [
                    'label'    => $row['label'] ?? '',
                    'question' => $row['question'] ?? '',
                    'evidence' => $evidenceItems,
                ];
            }
        }

        // Build performance rows — rename 'summary' → 'evidence' for the edit form
        $performanceRows = [];
        foreach ($reportSections['performance_rows'] ?? [] as $row) {
            $performanceRows[] = [
                'parameter'    => (string) ($row['parameter'] ?? ''),
                'level'        => (string) ($row['level'] ?? ''),
                'rubric_id'    => $row['rubric_id'] ?? null,
                'rubric_score' => $row['rubric_score'] ?? null,
                'evidence'     => (string) ($row['summary'] ?? ''),
            ];
        }

        return [
            'overview'         => $overview,
            'performance_rows' => $performanceRows,
            'scenario_rows'    => $structuredScenarioRows,
            'summary'          => (string) ($reportSections['summary_text'] ?? ''),
            'graph_rubrics'    => $reportSections['graph_rubrics'] ?? [],
        ];
    }

    /**
     * Convert report_data JSON back into the $reportSections format consumed by report_pdf.blade.php.
     */
    protected function reportDataToSections(array $reportData): array
    {
        $performanceRows = [];
        foreach ($reportData['performance_rows'] ?? [] as $row) {
            $performanceRows[] = [
                'parameter'    => (string) ($row['parameter'] ?? ''),
                'level'        => (string) ($row['level'] ?? ''),
                'summary'      => (string) ($row['evidence'] ?? ''),
                'rubric_id'    => $row['rubric_id'] ?? null,
                'rubric_score' => $row['rubric_score'] ?? null,
            ];
        }

        $scenarioRows = [];
        foreach ($reportData['scenario_rows'] ?? [] as $row) {
            $evidenceStrings = [];
            foreach ($row['evidence'] ?? [] as $item) {
                $param = (string) ($item['parameter'] ?? '');
                $level = (string) ($item['level'] ?? '');
                $text  = (string) ($item['text'] ?? '');
                if ($level !== '' && $text !== '') {
                    $evidenceStrings[] = $param . ': ' . $level . ' - ' . $text;
                } elseif ($level !== '') {
                    $evidenceStrings[] = $param . ': ' . $level;
                } else {
                    $evidenceStrings[] = $param . ': ' . $text;
                }
            }
            $scenarioRows[] = [
                'label'    => (string) ($row['label'] ?? ''),
                'question' => (string) ($row['question'] ?? ''),
                'evidence' => $evidenceStrings,
            ];
        }

        return [
            'performance_rows' => $performanceRows,
            'scenario_rows'    => $scenarioRows,
            'summary_text'     => (string) ($reportData['summary'] ?? ''),
            'graph_rubrics'    => $reportData['graph_rubrics'] ?? [],
        ];
    }

    protected function extractJsonBlock(string $text): string
    {
        $clean = trim($text);
        if ($clean === '') {
            return '';
        }
        $clean = preg_replace('/^```(?:json)?/i', '', $clean);
        $clean = preg_replace('/```$/', '', $clean);
        $first = strpos($clean, '{');
        $last  = strrpos($clean, '}');
        if ($first === false || $last === false || $last <= $first) {
            return '';
        }
        return substr($clean, $first, $last - $first + 1);
    }

    protected function parseSectionAJson($section, array $questionTexts = []): array
    {
        $rows = [];
        if (!is_array($section)) {
            return $rows;
        }
        foreach ($section as $questionLabel => $params) {
            if (!is_array($params)) {
                continue;
            }
            $evidence = [];
            foreach ($params as $paramName => $value) {
                $label = trim((string) $paramName);
                if (strpos($label, ' - ') !== false) {
                    $label = trim(explode(' - ', $label, 2)[0]);
                }
                $evidence[] = $label . ': ' . trim((string) $value);
            }
            $rows[] = [
                'label'    => (string) $questionLabel,
                'question' => $questionTexts[$questionLabel] ?? '',
                'evidence' => $evidence,
            ];
        }
        return $rows;
    }

    protected function parseSectionBJson($section): array
    {
        $rows = [];
        if (!is_array($section)) {
            return $rows;
        }
        foreach ($section as $paramName => $data) {
            if (!is_array($data)) {
                continue;
            }
            $label = trim((string) $paramName);
            if (strpos($label, ' - ') !== false) {
                $label = trim(explode(' - ', $label, 2)[0]);
            }
            $rows[] = [
                'parameter'    => $label,
                'level'        => (string) ($data['Overall Level'] ?? ''),
                'summary'      => (string) ($data['Summary'] ?? ''),
                'rubric_id'    => null,
                'rubric_score' => null,
            ];
        }
        return $rows;
    }

    protected function buildPerformanceRowsFromSectionAJson($sectionA, $sectionB, ?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        if (!is_array($sectionA)) {
            return [];
        }

        $rubricDefs = $this->getRubricDefinitionsForAssignment($assignmentRow);
        if (empty($rubricDefs['ordered'])) {
            return [];
        }

        $sectionBSummaryMap = $this->getSectionBSummaryMap($sectionB);
        $parameterBuckets   = [];

        foreach ($sectionA as $params) {
            if (!is_array($params)) {
                continue;
            }
            foreach ($params as $paramName => $value) {
                $label           = $this->stripParameterDescription((string) $paramName);
                $normalizedLabel = $this->normalizeLabel($label);
                if ($normalizedLabel === '') {
                    continue;
                }
                $rubric = $this->resolveRubricFromEvidenceValue((string) $value, $rubricDefs);
                if (!$rubric) {
                    continue;
                }
                if (!isset($parameterBuckets[$normalizedLabel])) {
                    $parameterBuckets[$normalizedLabel] = ['parameter' => $label, 'scores' => []];
                }
                $parameterBuckets[$normalizedLabel]['scores'][] = (int) $rubric['score'];
            }
        }

        return $this->finalizePerformanceRows($parameterBuckets, $sectionBSummaryMap, $rubricDefs);
    }

    protected function buildPerformanceRowsFromScenarioRows(array $scenarioRows, array $sectionBRows = [], ?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        if (empty($scenarioRows)) {
            return [];
        }

        $rubricDefs = $this->getRubricDefinitionsForAssignment($assignmentRow);
        if (empty($rubricDefs['ordered'])) {
            return [];
        }

        $sectionBSummaryMap = [];
        foreach ($sectionBRows as $row) {
            $label = $this->normalizeLabel((string) ($row['parameter'] ?? ''));
            if ($label === '') {
                continue;
            }
            $sectionBSummaryMap[$label] = (string) ($row['summary'] ?? '');
        }

        $parameterBuckets = [];
        foreach ($scenarioRows as $row) {
            foreach (($row['evidence'] ?? []) as $line) {
                $line = trim((string) $line);
                if ($line === '') {
                    continue;
                }
                $leftParts = explode(' - ', $line, 2);
                $left      = trim((string) ($leftParts[0] ?? ''));
                $labelParts = explode(':', $left, 2);
                $label     = $this->stripParameterDescription((string) ($labelParts[0] ?? ''));
                $normalizedLabel = $this->normalizeLabel($label);
                if ($normalizedLabel === '') {
                    continue;
                }
                $rubric = $this->resolveRubricFromEvidenceValue($line, $rubricDefs);
                if (!$rubric) {
                    continue;
                }
                if (!isset($parameterBuckets[$normalizedLabel])) {
                    $parameterBuckets[$normalizedLabel] = ['parameter' => $label, 'scores' => []];
                }
                $parameterBuckets[$normalizedLabel]['scores'][] = (int) $rubric['score'];
            }
        }

        return $this->finalizePerformanceRows($parameterBuckets, $sectionBSummaryMap, $rubricDefs);
    }

    protected function finalizePerformanceRows(array $parameterBuckets, array $sectionBSummaryMap, array $rubricDefs): array
    {
        $rows = [];
        foreach ($parameterBuckets as $normalizedLabel => $bucket) {
            $scores = array_values(array_filter($bucket['scores'] ?? [], 'is_numeric'));
            if (empty($scores)) {
                continue;
            }
            $averageScore = (int) round(array_sum($scores) / count($scores));
            $rubric       = $this->findRubricByAverageScore($averageScore, $rubricDefs['ordered']);
            if (!$rubric) {
                continue;
            }
            $rows[] = [
                'parameter'    => (string) ($bucket['parameter'] ?? ''),
                'level'        => (string) ($rubric['name'] ?? ''),
                'summary'      => (string) ($sectionBSummaryMap[$normalizedLabel] ?? ''),
                'rubric_id'    => (int) ($rubric['id'] ?? 0) ?: null,
                'rubric_score' => (int) ($rubric['score'] ?? 0) ?: null,
            ];
        }
        return $rows;
    }

    protected function getRubricDefinitionsForAssignment(?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        $query = RealQAssessmentRubric::query();
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)) {
            $query->where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id);
        }

        $rubrics = $query->orderBy('score')->get(['id', 'name', 'score']);
        $ordered = [];
        $byName  = [];

        foreach ($rubrics as $rubric) {
            $name  = trim((string) ($rubric->name ?? ''));
            $score = (int) ($rubric->score ?? 0);
            if ($name === '' || $score <= 0) {
                continue;
            }
            $item = [
                'id'              => (int) $rubric->id,
                'name'            => $name,
                'score'           => $score,
                'normalized_name' => $this->normalizeLabel($name),
            ];
            $ordered[]                           = $item;
            $byName[$item['normalized_name']]    = $item;
        }

        return ['ordered' => $ordered, 'by_name' => $byName];
    }

    protected function getSectionBSummaryMap($sectionB): array
    {
        $summaryMap = [];
        if (!is_array($sectionB)) {
            return $summaryMap;
        }
        foreach ($sectionB as $paramName => $data) {
            if (!is_array($data)) {
                continue;
            }
            $label = $this->normalizeLabel($this->stripParameterDescription((string) $paramName));
            if ($label === '') {
                continue;
            }
            $summaryMap[$label] = (string) ($data['Summary'] ?? '');
        }
        return $summaryMap;
    }

    protected function resolveRubricFromEvidenceValue(string $value, array $rubricDefs): ?array
    {
        $value = trim($value);
        if ($value === '' || empty($rubricDefs['ordered'])) {
            return null;
        }

        $beforeDash = trim(explode(' - ', $value, 2)[0]);
        $candidates = array_filter([
            $this->normalizeLabel($beforeDash),
            $this->normalizeLabel((string) preg_replace('/^.*?:\s*/', '', $beforeDash)),
            $this->normalizeLabel($value),
        ]);

        foreach ($candidates as $candidate) {
            if (isset($rubricDefs['by_name'][$candidate])) {
                return $rubricDefs['by_name'][$candidate];
            }
        }

        foreach ($rubricDefs['ordered'] as $rubric) {
            $rubricName = (string) ($rubric['normalized_name'] ?? '');
            foreach ($candidates as $candidate) {
                if ($rubricName !== '' && (str_contains($candidate, $rubricName) || str_contains($rubricName, $candidate))) {
                    return $rubric;
                }
            }
        }

        return null;
    }

    protected function findRubricByAverageScore(int $averageScore, array $orderedRubrics): ?array
    {
        if (empty($orderedRubrics)) {
            return null;
        }
        $closest    = null;
        $smallestGap = null;
        foreach ($orderedRubrics as $rubric) {
            $gap = abs(((int) ($rubric['score'] ?? 0)) - $averageScore);
            if ($closest === null || $gap < $smallestGap) {
                $closest     = $rubric;
                $smallestGap = $gap;
            }
        }
        return $closest;
    }

    protected function stripParameterDescription(string $label): string
    {
        $label = trim($label);
        if (strpos($label, ' - ') !== false) {
            return trim(explode(' - ', $label, 2)[0]);
        }
        return $label;
    }

    protected function storeRealQParameterScores($student, AssessmentStudentReport $reportRow, array $performanceRows, ?RealQAssessmentSchoolAssignment $assignmentRow = null): void
    {
        if (empty($performanceRows)) {
            return;
        }

        $paramMap   = [];
        $paramQuery = RealQAssessmentParameter::query();
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_parameters_id)) {
            $paramIds = array_values(array_filter(array_map('intval', explode(',', (string) $assignmentRow->realq_assessment_assigned_parameters_id))));
            if (!empty($paramIds)) {
                $paramQuery->whereIn('id', $paramIds);
            }
        }
        foreach ($paramQuery->get() as $param) {
            $name = trim((string) ($param->name ?? ''));
            if ($name === '') {
                continue;
            }
            $paramMap[$this->normalizeLabel($name)] = (int) $param->id;
        }

        $rubricMap   = [];
        $rubricQuery = RealQAssessmentRubric::query();
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)) {
            $rubricQuery->where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id);
        }
        foreach ($rubricQuery->get() as $rubric) {
            $name = trim((string) ($rubric->name ?? ''));
            if ($name === '') {
                continue;
            }
            $rubricMap[$this->normalizeLabel($name)] = (int) $rubric->id;
        }

        $generateTimeRubricByParam = RealQStudentParameterScore::where('assessment_report_id', $reportRow->id)
            ->whereNotNull('generate_time_rubric_id')
            ->pluck('generate_time_rubric_id', 'parameter_id')
            ->all();

        RealQStudentParameterScore::where('assessment_report_id', $reportRow->id)->delete();

        $rows = [];
        foreach ($performanceRows as $row) {
            $paramName = $this->normalizeLabel((string) ($row['parameter'] ?? ''));
            if ($paramName === '' || !isset($paramMap[$paramName])) {
                continue;
            }
            $rubricId = (int) ($row['rubric_id'] ?? 0);
            if ($rubricId <= 0) {
                $levelName = $this->normalizeLabel((string) ($row['level'] ?? ''));
                $rubricId  = $rubricMap[$levelName] ?? $this->matchClosestRubricId($levelName, $rubricMap);
            }
            $parameterId = (int) $paramMap[$paramName];
            $generateTimeRubricId = $generateTimeRubricByParam[$parameterId] ?? $rubricId;
            $rows[] = [
                'student_id'              => (int) ($student->id ?? 0),
                'school_id'               => (int) (optional($student->school)->id ?? 0) ?: null,
                'student_grade_id'        => (int) ($student->student_grade_id ?? 0) ?: null,
                'assessment_report_id'    => (int) $reportRow->id,
                'parameter_id'            => $parameterId,
                'rubric_id'               => $rubricId ?: null,
                'generate_time_rubric_id' => $generateTimeRubricId ?: null,
                'created_at'              => now(),
                'updated_at'              => now(),
            ];
        }

        if (!empty($rows)) {
            RealQStudentParameterScore::insert($rows);
        }
    }

    protected function normalizeLabel(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return $value;
    }

    protected function matchClosestRubricId(string $levelName, array $rubricMap): ?int
    {
        if ($levelName === '' || empty($rubricMap)) {
            return null;
        }
        foreach ($rubricMap as $name => $id) {
            if (str_contains($levelName, $name) || str_contains($name, $levelName)) {
                return (int) $id;
            }
        }
        return null;
    }

    protected function parseSectionAQuestions(string $section): array
    {
        $rows = [];
        if ($section === '') {
            return $rows;
        }
        $lines   = array_values(array_filter(array_map('trim', preg_split('/\R+/', $section))));
        $current = null;
        foreach ($lines as $line) {
            if (stripos($line, 'Section A:') === 0) {
                continue;
            }
            if (preg_match('/^Question\s*(\d+)\s*[:\-]?/i', $line, $match)) {
                if ($current) {
                    $rows[] = $current;
                }
                $current = ['label' => 'Question ' . $match[1], 'evidence' => []];
                continue;
            }
            if (!$current) {
                continue;
            }
            $current['evidence'][] = $line;
        }
        if ($current) {
            $rows[] = $current;
        }
        return $rows;
    }

    protected function parseSectionBParameters(string $section): array
    {
        $rows = [];
        if ($section === '') {
            return $rows;
        }
        $lines   = array_values(array_filter(array_map('trim', preg_split('/\R+/', $section))));
        $current = null;
        foreach ($lines as $line) {
            if (stripos($line, 'Section B:') === 0) {
                continue;
            }
            if (preg_match('/^\d+\.\s*(.+)$/', $line, $match)) {
                if ($current) {
                    $rows[] = $current;
                }
                $label   = trim($match[1]);
                $name    = trim(preg_split('/\s*-\s*/', $label, 2)[0]);
                $current = ['parameter' => $name !== '' ? $name : $label, 'level' => '', 'summary' => ''];
                continue;
            }
            if (!$current) {
                continue;
            }
            if (preg_match('/^[-•]?\s*Overall Level[:\s-]*(.+)$/i', $line, $match)) {
                $current['level'] = trim($match[1]);
                continue;
            }
            if (preg_match('/^[-•]?\s*Summary[:\s-]*(.+)$/i', $line, $match)) {
                $current['summary'] = trim($match[1]);
                continue;
            }
            if ($current['summary'] === '' && $current['level'] !== '') {
                $current['summary'] = $line;
            }
        }
        if ($current) {
            $rows[] = $current;
        }
        return $rows;
    }

    protected function parseSectionCSummary(string $section): string
    {
        if ($section === '') {
            return '';
        }
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $section))));
        $lines = array_values(array_filter($lines, function ($line) {
            if (stripos($line, 'Section C:') === 0) {
                return false;
            }
            if (stripos($line, 'A short, clear narrative') === 0) {
                return false;
            }
            if (preg_match('/^[-•]/', $line)) {
                return false;
            }
            return true;
        }));
        return trim(implode("\n", $lines));
    }

    protected function extractNarrativeFromReport(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        $pattern = '/Section C:.*?\R+/i';
        if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            $start     = $matches[0][1] + strlen($matches[0][0]);
            $candidate = trim(substr($text, $start));
            if ($candidate !== '') {
                return $candidate;
            }
        }
        return $text;
    }
}
