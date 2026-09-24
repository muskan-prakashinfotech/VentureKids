<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $partner_name }} joins VentureKids</title>
    </head>

    <body>
        <p style="font-weight: 600;margin: 0;">Hi {{ $partner_name }}</p>
        <p>We have created your Partner profile on VentureKids.</p>
        <p>You can log in and start managing your assigned team and trainers right away.</p>
        <p>License Purchased - {{ $license_count }}</p>
        <p>Use the link below to log in and get started.</p>
        <p><a href="{{ $domain }}">{{ $domain }}</a></p>
        <ul>
            <li>Username - {{ $username }}</li>
            <li>Password - {{ $password }}</li>
        </ul>
        <p>Please reach out to us if you have any questions.</p>
        <p>Best Regards,<br>VentureKids Team</p>
    </body>
</html>
