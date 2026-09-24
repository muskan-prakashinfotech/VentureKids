<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VentureKids</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6;">
    <p>Hi {{ $name }},</p>

    <p>
        We regret to inform you that the upcoming session scheduled on <strong>{{ \Carbon\Carbon::parse($external_session->date_time, 'UTC')->timezone($timeZone)->format('d M Y') }}</strong> at <strong>{{ \Carbon\Carbon::parse($external_session->date_time, 'UTC')->timezone($timeZone)->format('h:i A T') }} </strong> has been cancelled.
    </p>

    <p>
        We apologize for any inconvenience.
    </p>

    <p>Thank you for your understanding.</p>

    <p>
        Best regards,<br>
        <strong>VentureKids Team</strong>
    </p>

</body>

</html>