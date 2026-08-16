@extends('layouts.print')

@section('title', 'تقرير سجل الحوادث')

@section('content')
    @include('reports.incidents-log._table')
@endsection
