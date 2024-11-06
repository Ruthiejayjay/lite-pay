@component('mail::message')
    # Hello, {{ $user->name }}!

    Please verify your email

    @component('mail::button', ['url' => $verificationUrl])
        Verify Email
    @endcomponent

    If you did not create an account, no further action is required.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
