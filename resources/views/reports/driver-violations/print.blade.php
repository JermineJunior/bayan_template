@extends('layouts.print')

@section('title', 'تقرير مخالفات السائقين')

@section('content')
    @include('reports.driver-violations._table')
@endsection
