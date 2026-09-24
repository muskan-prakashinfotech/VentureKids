<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>School Updated by Partner</title>
</head>
<body>
    <p style="font-weight: 600; margin: 0;">Hi Super Admin,</p>
    <p>The partner below updated a school profile and changed one or more restricted fields.</p>

    <p>
        <strong>School:</strong> {{ $schoolName }}<br>
        <strong>Partner:</strong> {{ $partnerName }} ({{ $partnerEmail }})
    </p>

    <p><strong>Changed Fields:</strong></p>
    <ul>
        @foreach ($changes as $change)
            <li>
                <strong>{{ $change['label'] }}</strong><br>
                Old: {{ $change['old'] }}<br>
                New: {{ $change['new'] }}
            </li>
        @endforeach
    </ul>

    <p>Please review the update if needed.</p>
</body>
</html>
