@php
$url = route('password.reset', [
'token' => $token,
'email' => $email,
]);
@endphp

<p>Klik link berikut untuk reset password Anda:</p>
<p>
    <a href="{{ $url }}">Reset Password</a>
</p>

<p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
