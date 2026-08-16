@extends('layouts.print')

@section('title', 'تقرير حالة التأمينات')

@section('content')
    @include('reports.insurance-status._table')
@endsection
