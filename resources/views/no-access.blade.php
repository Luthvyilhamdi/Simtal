@extends('layouts.app')
@section('title', 'Belum Ada Akses')
@section('breadcrumb', 'Akses')

@section('content')
<div style="max-width:520px;margin:60px auto;text-align:center;background:#fff;border:1px solid var(--card-border);border-radius:16px;box-shadow:var(--card-shadow);padding:40px 32px;">
    <div style="width:64px;height:64px;border-radius:50%;background:#fffbeb;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
        <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#d97706" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>
    <div style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;">Belum ada akses menu</div>
    <p style="font-size:13.5px;color:#6b7280;line-height:1.6;margin-bottom:6px;">
        Akun Anda belum diberi akses ke menu mana pun. Silakan hubungi <strong>Super Admin</strong>
        untuk mengatur menu yang dapat Anda buka.
    </p>
</div>
@endsection
