@extends('layouts.app')
@section('title', 'Master Jabatan')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Jabatan')
@section('content')
@include('master._table', [
    'title'        => 'Jabatan',
    'subtitle'     => 'Kelola data jabatan karyawan',
    'field'        => 'nama_jabatan',
    'placeholder'  => 'Nama Jabatan',
    'routeStore'   => route('master.jabatan.store'),
    'routeUpdate'  => fn($item) => route('master.jabatan.update', $item),
    'routeDestroy' => fn($item) => route('master.jabatan.destroy', $item),
    'data'         => $data,
])
@endsection