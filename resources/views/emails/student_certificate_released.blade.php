<!DOCTYPE html>
<html>
<head>
    <title>Congratulations! Your Certificate is Ready</title>
</head>
<body>
    <h2>Congratulations! Your Certificate is Ready</h2>

    <p>Dear {{ $certificate->student->name }},</p>

    <p>Congratulations! Your certificate has been successfully released.</p>

    <ul>
        <li><strong>Level:</strong> {{ $certificate->grade->grade }}</li>
        <li><strong>Unique ID:</strong> {{ $certificate->unique_id }}</li>
        <li><strong>Issue Date:</strong> {{ $certificate->issue_date }}</li>
    </ul>

    <p>Download your certificate by logging in to the platform.</p>
    

    <p>Best regards,<br>
    VentureKids Team</p>
</body>
</html>