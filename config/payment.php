<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Summary Password
    |--------------------------------------------------------------------------
    |
    | Password khusus untuk melihat detail ringkasan pembayaran.
    | Password ini berbeda dengan password login akun.
    |
    */
    'summary_password' => env('PAYMENT_SUMMARY_PASSWORD', 'rahasia123'),

    /*
    |--------------------------------------------------------------------------
    | Session Timeout
    |--------------------------------------------------------------------------
    |
    | Durasi session password dalam menit.
    | Setelah timeout, user harus memasukkan password lagi.
    |
    */
    'session_timeout' => env('PAYMENT_SUMMARY_SESSION_TIMEOUT', 30), // 30 menit
];
