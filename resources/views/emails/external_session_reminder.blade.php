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
        @php($occurrenceDateTimeToShow = $occurrence_date_time ?? $external_session->date_time)
        Just a quick reminder! You have a <strong>VentureKids session </strong> scheduled in <strong>1 hour at {{ \Carbon\Carbon::parse($occurrenceDateTimeToShow, 'UTC')->timezone($timeZone)->format('h:i A') }} </strong> today.
        
    </p>
    <p>
        Please ensure you're logged in on time. See you soon!
    </p>

    <p>
        <strong>— Team VentureKids</strong>
    </p>

</body>

</html>