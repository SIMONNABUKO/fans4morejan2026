@component('mail::message')
# Update on Your Creator Application

We have reviewed your creator application. Unfortunately, at this time, we are unable to approve your application.

@if($feedback)
## Feedback from the Admin Team:
{{ $feedback }}
@endif

You can update your application with the requested changes and submit it again for review.

@component('mail::button', ['url' => config('app.frontend_url') . '/creator-application'])
Update Application
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 