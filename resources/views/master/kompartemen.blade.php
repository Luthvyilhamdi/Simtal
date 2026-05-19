@extends('layouts.app')
@section('title', 'Master Kompartemen')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Kompartemen')
@section('content')
@include('master._table', [
    'title'        => 'Kompartemen',
    'subtitle'     => 'Kelola data kompartemen',
    'field'        => 'nama_kompartemen',
    'placeholder'  => 'Nama Kompartemen',
    'routeStore'   => route('master.kompartemen.store'),
    'routeUpdate'  => fn($item) => route('master.kompartemen.update', $item),
    'routeDestroy' => fn($item) => route('master.kompartemen.destroy', $item),
    'data'         => $data,
])
@endsection