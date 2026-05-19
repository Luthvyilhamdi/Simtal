@extends('layouts.app')
@section('title', 'Master Person Grade')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Person Grade')
@section('content')
@include('master._table', [
    'title'        => 'Person Grade',
    'subtitle'     => 'Kelola data person grade',
    'field'        => 'person_grade',
    'placeholder'  => 'Person Grade',
    'routeStore'   => route('master.person-grade.store'),
    'routeUpdate'  => fn($item) => route('master.person-grade.update', $item),
    'routeDestroy' => fn($item) => route('master.person-grade.destroy', $item),
    'data'         => $data,
])
@endsection