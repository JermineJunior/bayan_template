@extends('layouts.print')

@section('title', 'تقرير المصروفات')

@section('content')
    @include('reports.expenses._table')

    <div class="total">
        إجمالي المصروفات: {{ number_format((float) $totalAmount, 2) }}
    </div>
@endsection
