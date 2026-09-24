<!DOCTYPE html>
<html>
<head>
    <title>Student Certificates Released</title>
</head>
<body>
    <h2>Student Certificates Released</h2>

    <p>Dear {{ $certificates[0]->trainer->trainer_name }},</p>

    <p>Certificates have been released for the students:</p>

    <p><strong>Level:</strong> {{ $grade->grade }}</p>
    <p><strong>Total Certificates:</strong> {{ count($certificates) }}</p>

    <p>You can download all certificates using the following link:</p>
    <p><a href="{{ $bulkDownloadUrl }}">Download All Certificates</a></p>

    <p><strong>Note:</strong> This bulk download link will expire in 7 days.</p>

    <p>Best regards,<br>
    VentureKids Team</p>
</body>
</html>