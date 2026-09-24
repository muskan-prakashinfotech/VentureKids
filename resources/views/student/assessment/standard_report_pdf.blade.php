<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Baseline Assessment Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .page {
            padding: 24px;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
        }
        .logo {
            height: 60px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            margin-top: 4px;
            color: #555;
            font-size: 16px;
            font-weight: bold;
        }
        .overview {
            border: 1px solid #ddd;
            background: #fafafa;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .overview-row {
            margin: 4px 0;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: top;
        }
        th {
            background: #f2f2f2;
            text-align: left;
        }
        .muted {
            color: #666;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <img src="{{ $schoolLogoPath }}" alt="School Logo" class="logo">
            <p class="title">{{ $student->name }}</p>
            <p class="subtitle">Baseline Assessment Report</p>
        </div>

        <div class="overview">
            <!-- <div class="overview-row"><strong>Student Name:</strong> {{ $student->name }}</div> -->
            <!-- <div class="overview-row"><strong>Attempt Date:</strong> {{ $attemptedOn }}</div> -->
            <div class="overview-row">
                <strong>Overview:</strong>
                This report shows your current level across different entrepreneurial skills, mindsets, and knowledge areas. These levels are not grades, they simply help you understand where you are today and where you can grow next. Every entrepreneur starts somewhere, and with practise, curiosity, and persistence, each of these abilities can continue to develop.
            </div>
        </div>

        <p class="section-title">Performance Evaluation</p>
        <table>
            <thead>
                <tr>
                    <th style="width: 24%;">Parameter</th>
                    <!-- <th style="width: 16%;">Total Score</th> -->
                    <th style="width: 20%;">Assessment</th>
                    <!-- <th style="width: 40%;">Evidence from Student Work</th> -->
                </tr>
            </thead>
            <tbody>
                @forelse($evaluationRows as $row)
                    <tr>
                        <td>{{ $row['parameter'] }}</td>
                        <!-- <td>{{ $row['total_score'] }}</td> -->
                        <td>{{ $row['rubric_level'] }}</td>
                        <!-- <td>{{ $row['evidence'] }}</td> -->
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No evaluation data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="section-title" style="margin-top: 16px;">Assessment Guide</p>
        <div class="overview">
            <div class="overview-row"><strong>Beginning</strong></div>
            <div class="overview-row">You're just getting started, keep exploring and practising to build this entrepreneurial skill.</div>

            <div class="overview-row" style="margin-top: 8px;"><strong>Developing</strong></div>
            <div class="overview-row">You are building momentum, a little more practise and experimentation will make you stronger.</div>

            <div class="overview-row" style="margin-top: 8px;"><strong>Promising</strong></div>
            <div class="overview-row">You are on the right track, with consistency and confidence, this skill can soon become one of your strengths.</div>

            <div class="overview-row" style="margin-top: 8px;"><strong>Proficient</strong></div>
            <div class="overview-row">You show strong ability here, keep applying it to develop ideas and solve real-world challenges.</div>

            <div class="overview-row" style="margin-top: 8px;"><strong>Excellent</strong></div>
            <div class="overview-row">Exceptional work, this strength shows strong entrepreneurial thinking and mindset.</div>
        </div>

        <!-- <p class="muted" style="margin-top:10px;">
            Scoring Interpretation: 3-4 Needs Improvement, 5-6 Developing, 7-9 Competent, 10-12 Strong.
        </p> -->
    </div>
</body>
</html>
