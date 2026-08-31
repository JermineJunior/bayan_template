@extends('layouts.print')

@section('title', 'تقرير المصروفات')

@section('content')
    @include('reports.expenses._table')

    <div class="total">
        إجمالي المصروفات: {{ money($totalAmount) }}
    </div>
@endsection
