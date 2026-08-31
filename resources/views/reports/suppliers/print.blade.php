@extends('layouts.print')

@section('title', 'تقرير الموردين')

@section('content')
    @include('reports.suppliers._table')

    <div class="total">
        إجمالي الفواتير: {{ money($totalInvoiced) }} — المدفوع: {{ money($totalPaid) }} — الرصيد: {{ money($totalBalance) }}
    </div>
@endsection
