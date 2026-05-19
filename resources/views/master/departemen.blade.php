@extends('layouts.app')
@section('title', 'Master Departemen')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Departemen')
@section('content')
@include('master._table', [
    'title'        => 'Departemen',
    'subtitle'     => 'Kelola data departemen',
    'field'        => 'nama_departemen',
    'placeholder'  => 'Nama Departemen',
    'routeStore'   => route('master.departemen.store'),
    'routeUpdate'  => fn($item) => route('master.departemen.update', $item),
    'routeDestroy' => fn($item) => route('master.departemen.destroy', $item),
    'data'         => $data,
])
@endsection