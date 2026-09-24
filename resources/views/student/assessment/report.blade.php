@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section>
        <div class="assessment-tab">
            <div class="pageTitle">
                <h2>Assessment</h2>
            </div>
        </div>

        <div class="card sa-report-shell">
            <div class="card-body">
                <div class="sa-report-header">
                    <div class="sa-report-metrics">
                        <!-- <span>Mode: <strong>{{ strtoupper($mode ?? 'SUBJECTIVE') }}</strong></span> -->
                        <span>Answered: <strong>{{ $answered }}</strong> / {{ $total }}</span>
                        <span>Completion: <strong>{{ $completion }}%</strong></span>
                    </div>
                    <button
                        type="button"
                        id="saReportDownloadBtn"
                        class="sa-report-download-btn"
                        data-url="{{ ($mode ?? 'subjective') === 'mcq' ? route('student.assessment.report.download', ['mode' => 'mcq']) : route('student.assessment.report.download') }}"
                        data-has-report="{{ !empty($reportText) ? '1' : '0' }}"
                        data-first-name="{{ $firstName }}"
                    >Download Report</button>
                </div>
                <div id="saReportStatus" class="sa-report-status mt-2" @if(!empty($reportText)) style="display:none;" @endif>Your report is generating...</div>

                <h3 id="saReportTitle" class="sa-report-title" @if(empty($reportText)) style="display:none;" @endif>
                    <span class="sa-report-greeting">Dear {{ $firstName }},</span>
                    <span class="sa-report-narrative">{{ $reportText }}</span>
                </h3>
            </div>
        </div>
    </section>
</div>

<script>
    (function () {
        const btn = document.getElementById('saReportDownloadBtn');
        const status = document.getElementById('saReportStatus');
        const title = document.getElementById('saReportTitle');
        if (!btn) return;

        const hasReport = btn.getAttribute('data-has-report') === '1';
        const firstName = btn.getAttribute('data-first-name') || 'Student';

        const downloadReport = async function () {
            const url = btn.getAttribute('data-url');
            if (!url || btn.disabled) return;

            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = 'Downloading...';

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    throw new Error('Failed to download report.');
                }

                const blob = await response.blob();
                const disposition = response.headers.get('content-disposition') || '';
                let filename = 'assessment_report.pdf';
                const match = disposition.match(/filename\*?=(?:UTF-8'')?\"?([^\";]+)\"?/i);
                if (match && match[1]) {
                    filename = decodeURIComponent(match[1].replace(/\+/g, '%20'));
                }

                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(downloadUrl);
            } catch (e) {
                swal('Error', 'Unable to download report right now. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        };

        const generateReport = async function () {
            const url = btn.getAttribute('data-url');
            if (!url || btn.disabled) return;

            btn.disabled = true;
            if (status) {
                status.style.display = 'block';
                status.textContent = 'Your report is generating...';
            }

            try {
                const response = await fetch(url + (url.indexOf('?') === -1 ? '?generate=1' : '&generate=1'), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    throw new Error('Failed to generate report.');
                }

                const data = await response.json();
                if (data && data.text && title) {
                    const greetingEl = title.querySelector('.sa-report-greeting');
                    const narrativeEl = title.querySelector('.sa-report-narrative');
                    if (greetingEl) {
                        greetingEl.textContent = `Dear ${firstName},`;
                    }
                    if (narrativeEl) {
                        narrativeEl.textContent = data.text;
                    }
                    title.style.display = 'block';
                    btn.setAttribute('data-has-report', '1');
                }
            } catch (e) {
                swal('Error', 'Unable to generate report right now. Please try again.', 'error');
            } finally {
                if (status) {
                    status.style.display = 'none';
                }
                btn.disabled = false;
            }
        };

        btn.addEventListener('click', downloadReport);

        // Auto-generate on first load (no auto-download).
        window.addEventListener('load', function () {
            if (btn.disabled || hasReport) {
                return;
            }
            generateReport();
        });
    })();
</script>
@endsection
