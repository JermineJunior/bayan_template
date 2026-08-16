@extends('layouts.print')

@section('title', 'تقرير نظرة عامة على الأسطول')

@section('content')
    @include('reports.fleet-overview._table')
@endsection
