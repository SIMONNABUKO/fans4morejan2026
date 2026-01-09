@component('mail::message')
# Congratulations! Your Creator Application has been Approved

Your application to become a creator has been approved. You can now start creating content and engaging with your fans.

@if($feedback)
## Feedback from the Admin Team:
{{ $feedback }}
@endif

@component('mail::button', ['url' => config('app.frontend_url')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 