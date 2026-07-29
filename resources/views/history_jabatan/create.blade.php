@extends('layouts.app')
@section('title', 'Tambah History Jabatan')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Tambah Jabatan')

@section('content')
<div class="form-wrap">
    <a href="{{ route('history_jabatan.index', $karyawan) }}" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke History Jabatan
    </a>

    <div class="page-header">
        <div class="page-title">➕ Tambah History Jabatan</div>
        <div class="page-sub">Tambah history jabatan baru untuk <strong>{{ $karyawan->nama }}</strong> — profil akan otomatis diperbarui</div>
    </div>

    @include('history_jabatan._form', [
        'action'      => route('history_jabatan.store', $karyawan),
        'method'      => 'POST',
        'submitLabel' => 'Simpan & Perbarui Profil',
        'history'     => null,
    ])
</div>
@endsection
