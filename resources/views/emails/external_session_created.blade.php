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
        Please note, a session has been scheduled with the <strong>VentureKids</strong> team.<br>
        We're excited to keep the momentum going with another opportunity to connect and collaborate!
    </p>

    <p><strong>Please find the details below:</strong></p>

    <ul style="list-style: none; padding: 0;">
        @php($occurrenceDateTimeToShow = $occurrence_date_time ?? $external_session->date_time)
        <li>🗓 <strong>Date:</strong> {{ \Carbon\Carbon::parse($occurrenceDateTimeToShow, 'UTC')->timezone($timeZone)->format('d M Y') }}</li>
        <li>⏰ <strong>Time:</strong> {{ \Carbon\Carbon::parse($occurrenceDateTimeToShow, 'UTC')->timezone($timeZone)->format('h:i A T') }}</li>
        @if($external_session->send_zoom_link)
            <li>📍 <strong>Link/Location:</strong> <a href="{{ $external_session->zoom_link }}">{{ $external_session->zoom_link }}</a></li>
        @endif
    </ul>

    <p>
        If you have any queries about the upcoming session, please reach out to the team directly.
    </p>

    <p>
        We look forward to your participation. See you soon!
    </p>

    <p>
        Best regards,<br>
        <strong>VentureKids Team</strong>
    </p>

</body>

</html>