@extends('layouts.app')
@section('title', 'Master Direktorat')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Direktorat')
@section('content')
@include('master._table', [
    'title'        => 'Direktorat',
    'subtitle'     => 'Kelola data direktorat',
    'field'        => 'nama_direktorat',
    'placeholder'  => 'Nama Direktorat',
    'routeStore'   => route('master.direktorat.store'),
    'routeUpdate'  => fn($item) => route('master.direktorat.update', $item),
    'routeDestroy' => fn($item) => route('master.direktorat.destroy', $item),
    'data'         => $data,
])
@endsection