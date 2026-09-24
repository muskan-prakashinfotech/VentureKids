<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VentureKids Change Password Notification</title>
</head>

<body>
    <p style="font-weight: 600;margin: 0;">Hi {{ $name }},</p>

    <p>Your VentureKids password has been changed.Click on this <a href="{{ route('login') }}">link</a> to login.
    <ul>
        <li>Email - {{ $email }}</li>
        <li>Password - {{ $password }}</li>
    </ul>
        <p style="margin: 0;">
        Warm regards,<br>
        VentureKids Team
    </p>
</body>

</html>
