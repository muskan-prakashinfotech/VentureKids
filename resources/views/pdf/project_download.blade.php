<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $displayTitle }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.55;
            margin: 28px;
            background: #ffffff;
        }
        .header {
            border: 1px solid #f3d3b0;
            border-left: 5px solid #F2994A;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 18px;
            background: #fffaf5;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px;
            color: #111827;
        }
        .meta {
            font-size: 11px;
            color: #6b7280;
            margin: 0;
        }
        .section {
            margin-bottom: 18px;
            border: 1px solid #f0c79b;
            border-left: 6px solid #F2994A;
            border-radius: 10px;
            padding: 14px 16px 12px;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .section h2 {
            font-size: 15px;
            margin: 0 0 12px;
            color: #F2994A;
            letter-spacing: 0.2px;
        }
        .question {
            margin-bottom: 12px;
            page-break-inside: avoid;
            padding: 10px 12px;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            background: #fcfcfd;
        }
        .question h3 {
            font-size: 12px;
            margin: 0 0 4px;
            color: #2f80ed;
            font-weight: 700;
        }
        .answer {
            margin: 0 0 6px;
            white-space: pre-wrap;
            color: #1f2937;
        }
        .attachments {
            margin: 0;
            padding-left: 18px;
            color: #4b5563;
        }
        .attachments li {
            margin-bottom: 2px;
        }
        .badge {
            display: inline-block;
            background: #fff1e6;
            color: #c2410c;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            margin-right: 6px;
        }
        .small-muted {
            color: #6b7280;
            font-size: 11px;
        }
        .section-meta {
            font-size: 11px;
            color: #9a3412;
            margin: -6px 0 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $displayTitle }}</h1>
        <p class="meta">
            <span class="badge">{{ $displayTheme }}</span>
        </p>
        <p class="meta">    
            {{ $studentName }} | {{ $studentSchool }} | {{ $studentBatch }} | {{ $lastSubmittedOn }}
        </p>
    </div>

    @foreach($sections as $section)
        <div class="section">
            <h2>{{ $section['section_title'] }}</h2>
            <!-- <div class="section-meta">{{ count($section['questions']) }} question(s) in this section</div> -->

            @foreach($section['questions'] as $question)
                <div class="question">
                    <h3>{{ $question['field_text'] }}</h3>
                    @if(!empty($question['has_answer']))
                        <p class="answer">{{ $question['answer_text'] }}</p>
                    @endif

                    @if(!empty($question['attachments']) && count($question['attachments']))
                        <div class="small-muted">Links:</div>
                        <ul class="attachments">
                            @foreach($question['attachments'] as $attachment)
                                @if(!empty($attachment['url']))
                                    <li>
                                        <a href="{{ $attachment['url'] }}">{{ $attachment['url'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif

                    @if(empty($question['has_answer']) && (empty($question['attachments']) || !count($question['attachments'])))
                        <p class="answer">No answer provided.</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
