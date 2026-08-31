@extends('layouts.print')

@section('title', 'تقرير قطع الغيار')

@section('content')
    @include('reports.spare-parts._table')

    <div class="total">
        عدد القطع: {{ number_format($totalParts) }} — منخفضة المخزون: {{ number_format($lowStockCount) }}
    </div>
@endsection
