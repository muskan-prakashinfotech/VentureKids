<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VentureKids Change Password Request Notification</title>
</head>

<body>
    <p style="font-weight: 600;margin: 0;">Hi {{ $school_name }},</p>

    <p>You are receiving this email because below student has requested to change their account password.</p>
    <p>Please click on this <a href="{{ $domain }}">link</a> to log in into your account and change password for below student from Student Management section.</p>

    <ul>
        <li>Student Name - {{ $student_name }}</li>
        <li>Student Email - {{ $student_email }}</li>
    </ul>

        <p style="margin: 0;">
        Warm regards,<br>
        VentureKids Team
    </p>
</body>

</html>
