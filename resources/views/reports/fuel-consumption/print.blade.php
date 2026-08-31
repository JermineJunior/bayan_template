@extends('layouts.print')

@section('title', 'تقرير استهلاك الوقود')

@section('content')
    @include('reports.fuel-consumption._table')

    <div class="total">
        إجمالي اللترات: {{ number_format((float) $totalLiters, 2) }}
        — إجمالي القيمة: {{ money($totalValue) }}
    </div>
@endsection
