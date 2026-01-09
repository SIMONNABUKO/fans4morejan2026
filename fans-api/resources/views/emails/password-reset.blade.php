@component('mail::message')
# Reset Your Password

Hello {{ $user->name }},

We received a request to reset your password for your Fans4More account.

Click the button below to reset your password. This link will expire in 60 minutes.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request a password reset, please ignore this email or contact support if you have concerns.

If the button doesn't work, copy and paste this link into your browser:
{{ $resetUrl }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
