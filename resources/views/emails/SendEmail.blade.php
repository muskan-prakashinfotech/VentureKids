@component('mail::message')

{!! $content['emailBody'] !!}

Thanks & Regards,<br/>
{{ config('app.name') }}
@endcomponent
