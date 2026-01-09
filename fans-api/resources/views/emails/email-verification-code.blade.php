@component('mail::message')
# Email Verification Code

Hello {{ $user->name }},

Your email verification code is:

# **{{ $code }}**

This code will expire in 10 minutes.

If you did not request this, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 