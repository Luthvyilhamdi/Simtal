@extends('layouts.app')
@section('title', 'Master Kode Struktur')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Kode Struktur')
@section('content')
@include('master._table', [
    'title'        => 'Kode Struktur',
    'subtitle'     => 'Kelola data kode struktur',
    'field'        => 'kode_struktur',
    'placeholder'  => 'Kode Struktur',
    'routeStore'   => route('master.kode-struktur.store'),
    'routeUpdate'  => fn($item) => route('master.kode-struktur.update', $item),
    'routeDestroy' => fn($item) => route('master.kode-struktur.destroy', $item),
    'data'         => $data,
])
@endsection