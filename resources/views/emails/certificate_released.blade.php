<!DOCTYPE html>
<html>
<head>
    <title>Student Certificate Released</title>
</head>
<body>
    <h2>Student Certificate Released</h2>

    <p>Dear {{ $certificate->trainer->trainer_name }},</p>

    <p>A certificate has been released for the following student:</p>

    <ul>
        <li><strong>Student:</strong> {{ $certificate->student->name }}</li>
        <li><strong>Level:</strong> {{ $certificate->grade->grade }}</li>
        <li><strong>Unique ID:</strong> {{ $certificate->unique_id }}</li>
        <li><strong>Issue Date:</strong> {{ $certificate->issue_date }}</li>
    </ul>

    <p>You can download the certificate using the following link:</p>
    <p><a href="{{ $downloadUrl }}">Download Certificate</a></p>

    <p><strong>Note:</strong> This download link will expire in 7 days.</p>

    <p>Best regards,<br>
    VentureKids Team</p>
</body>
</html>