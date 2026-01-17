@extends('exports.pdf.layout')

@section('title', 'Sales Report')
@section('report-title', 'Sales Report')

@section('content')
    <div class="report-info">
        <table>
            <tr>
                <td>Report Period:</td>
                <td>{{ $startDate }} to {{ $endDate }}</td>
            </tr>
            @if($section)
                <tr>
                    <td>Section:</td>
                    <td>{{ $section->name }}</td>
                </tr>
            @endif
            <tr>
                <td>Total Sales:</td>
                <td>{{ $sales->count() }}</td>
            </tr>
        </table>
    </div>

    <h2>Sales Transactions</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Section</th>
                <th>Payment</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Cost</th>
                <th class="text-right">Profit</th>
                <th class="text-right">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                @php
                    $revenue = $sale->items->sum(fn($item) => $item->quantity * $item->unit_price);
                    $cost = $sale->items->sum(fn($item) => $item->quantity * $item->cost_price);
                    $profit = $revenue - $cost;
                    $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->sale_date }}</td>
                    <td>{{ $sale->section->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td class="text-right">{{ number_format($revenue, 2) }}</td>
                    <td class="text-right">{{ number_format($cost, 2) }}</td>
                    <td class="text-right">{{ number_format($profit, 2) }}</td>
                    <td class="text-right">{{ number_format($margin, 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">
            <span>Total Revenue:</span>
            <span>{{ number_format($totalRevenue, 2) }}</span>
        </div>
        <div class="summary-item">
            <span>Total Cost:</span>
            <span>{{ number_format($totalCost, 2) }}</span>
        </div>
        <div class="summary-item">
            <span>Total Profit:</span>
            <span>{{ number_format($totalProfit, 2) }}</span>
        </div>
        <div class="summary-item">
            <span>Average Profit Margin:</span>
            <span>{{ number_format($avgMargin, 2) }}%</span>
        </div>
    </div>
@endsection