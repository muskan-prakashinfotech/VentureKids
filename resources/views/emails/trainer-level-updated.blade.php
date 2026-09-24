<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Trainer Level Update Alert</title>
</head>
<body>
    <p style="font-weight: 600; margin: 0;">Hi Super Admin,</p>
    <p>A partner has {{ $action === 'created' ? 'assigned' : 'changed' }} trainer level access.</p>

    <p>
        <strong>Trainer:</strong> {{ $trainerName }}<br>
        <strong>Partner:</strong> {{ $partnerName }} ({{ $partnerEmail }})
    </p>

    <p><strong>Level Change:</strong></p>
    <ul>
        <li><strong>Old:</strong> {{ $oldLevels }}</li>
        <li><strong>New:</strong> {{ $newLevels }}</li>
    </ul>

    <p>Please review if needed.</p>
</body>
</html>
