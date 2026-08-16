@extends('layouts.print')

@section('title', 'تقرير سجل تغيير الزيوت والفلاتر')

@section('content')
    @include('reports.oil-filter-changes._table')
@endsection
