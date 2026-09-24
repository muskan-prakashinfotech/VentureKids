@component('mail::message')

<p> Challenge Name : {!! $content['emailBody']['challenge_name'] !!} </p>
<p> Start Date : {!! $content['emailBody']['challenge_date'] !!} </p>
<p> Last Date To Submit : {!! $content['emailBody']['challenge_last_date'] !!} </p>

Thanks & Regards,<br/>
{{ config('app.name') }}
@endcomponent
