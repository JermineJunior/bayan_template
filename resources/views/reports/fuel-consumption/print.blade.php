@extends('layouts.print')

@section('title', 'تقرير استهلاك الوقود')

@section('content')
    @include('reports.fuel-consumption._table')

    <div class="total">
        إجمالي اللترات: {{ number_format((float) $totalLiters, 2) }}
        — إجمالي القيمة: {{ number_format((float) $totalValue, 2) }}
    </div>
@endsection
