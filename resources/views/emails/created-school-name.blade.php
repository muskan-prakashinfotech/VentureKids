<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $school_name }} joins VentureKids</title>
    </head>

    <body>
        <p style="font-weight: 600;margin: 0;">Hi {{ $principal_name }}</p>
        <p>We have created your {{ $school_name }} profile on VentureKids.</p>
        <p>You can complete the registration process and get your students started on this wonderful journey of Entrepreneurship.</p>
        <p>Course Start Date - {{ $course_start_date }} <br> Course Expiration Date - {{ $course_end_date }} </p>
        <p>Complete the registration using the <a href="{{ $domain }}">link</a> below and get started. Use below credentials to login. </p>
        <ul>
            <li>Username - {{ $username }}</li>
            <li>Password - {{ $password }}</li>
        </ul>
        <p>Please reach out to us if you have any questions.</p>
            <p style="margin: 0;">
        Warm regards,<br>
        VentureKids Team
    </p>
</body>
</html>
