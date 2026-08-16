@extends('layouts.print')

@section('title', 'تقرير تكاليف الصيانة')

@section('content')
    @include('reports.maintenance-cost._table')

    <div class="total">
        إجمالي التكاليف: {{ number_format((float) $totalCost, 2) }}
    </div>
@endsection
