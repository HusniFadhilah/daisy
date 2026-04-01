@extends('emails.template')

@php
$title = $headerTitle;
$preheader = $preheader;
$headerTitle = $headerTitle;
@endphp

@section('content')
<p>Kepada Yth,</p>

<p style="font-size:16px;">
    {!! $recipientName !!}
</p>

@if($contextInfo)
<p style="color:#666;">{{ $contextInfo }}</p>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;white-space:pre-line;">{{ $pesanReminder }}</td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;
                      padding:12px 28px;border-radius:6px;font-size:14px;
                      display:inline-block;font-weight:bold;">
                {{ $actionLabel }}
            </a>
        </td>
    </tr>
</table>
@endsection
