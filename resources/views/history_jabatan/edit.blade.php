@extends('layouts.app')
@section('title', 'Edit History Jabatan')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Edit Jabatan')

@section('content')
<div class="form-wrap">
    <a href="{{ route('history_jabatan.index', $karyawan) }}" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke History Jabatan
    </a>

    <div class="page-header">
        <div class="page-title">✏️ Edit History Jabatan</div>
        <div class="page-sub">Ubah data jabatan <strong>{{ $karyawan->nama }}</strong> — jabatan saat ini & profil akan dihitung ulang otomatis</div>
    </div>

    @include('history_jabatan._form', [
        'action'      => route('history_jabatan.update', [$karyawan, $historyJabatan]),
        'method'      => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
        'history'     => $historyJabatan,
    ])
</div>
@endsection
