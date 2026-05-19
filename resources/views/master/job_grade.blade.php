@extends('layouts.app')
@section('title', 'Master Job Grade')
@section('breadcrumb-parent', 'Master Data')
@section('breadcrumb', 'Job Grade')
@section('content')
@include('master._table', [
    'title'        => 'Job Grade',
    'subtitle'     => 'Kelola data job grade',
    'field'        => 'job_grade',
    'placeholder'  => 'Job Grade',
    'routeStore'   => route('master.job-grade.store'),
    'routeUpdate'  => fn($item) => route('master.job-grade.update', $item),
    'routeDestroy' => fn($item) => route('master.job-grade.destroy', $item),
    'data'         => $data,
])
@endsection