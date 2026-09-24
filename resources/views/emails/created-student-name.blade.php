<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Joins VentureKids</title>
</head>

<body>
    <p style="font-weight: 600;margin: 0;">Hi {{ $studentName }},</p>
    @if (!$isRegistred)
    <p>Congratulations on starting your Entrepreneurial journey with VentureKids.</p>
    <p style="font-weight: 600;">The skills essential for being an entrepreneur should be inculcated from a young age
        through schools.</p>
    <p>That's why we created VentureKids, so kids can have an entrepreneurial mindset early on.</p>
    <p>Now, you're ready to experience it yourself! Click on this <a href="{{ $domain }}">link</a> to get started
    </p>
    <ul>
        <li>Username - {{ $userName }}</li>
        @if(!empty($email))
        <li>Email - {{ $email }}</li>
        @endif
        <li>Password - {{ $password }}</li>
    </ul>
    @else
    <p>You are already registred with VentureKids, Kindly use your existing credentials to log on to the VentureKids.</p>
    @endif
        <p style="margin: 0;">
        Warm regards,<br>
        VentureKids Team
    </p>
</body>

</html>
