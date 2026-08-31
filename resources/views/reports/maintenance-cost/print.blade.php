@extends('layouts.print')

@section('title', 'تقرير تكاليف الصيانة')

@section('content')
    @include('reports.maintenance-cost._table')

    <div class="total">
        إجمالي التكاليف: {{ money($totalCost) }}
    </div>
@endsection
