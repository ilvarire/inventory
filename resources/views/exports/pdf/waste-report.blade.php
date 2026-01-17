@extends('exports.pdf.layout')

@section('title', 'Waste Report')
@section('report-title', 'Waste Report')

@section('content')
    <div class="report-info">
        <table>
            <tr>
                <td>Report Period:</td>
                <td>{{ $startDate }} to {{ $endDate }}</td>
            </tr>
            <tr>
                <td>Total Waste Incidents:</td>
                <td>{{ $wasteLogs->count() }}</td>
            </tr>
        </table>
    </div>

    <h2>Waste Logs</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Section</th>
                <th>Type</th>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th>Reason</th>
                <th class="text-right">Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wasteLogs as $waste)
                @php
                    $item = $waste->waste_type === 'raw_material'
                        ? ($waste->rawMaterial->name ?? 'N/A')
                        : ($waste->productionLog->recipeVersion->recipe->name ?? 'N/A');
                @endphp
                <tr>
                    <td>{{ $waste->created_at->format('Y-m-d') }}</td>
                    <td>{{ $waste->section->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $waste->waste_type)) }}</td>
                    <td>{{ $item }}</td>
                    <td class="text-right">{{ $waste->quantity }}</td>
                    <td>
                        <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $waste->reason)) }}</span>
                    </td>
                    <td class="text-right">{{ number_format($waste->cost_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Waste Summary by Reason</h3>
        @foreach($wasteByReason as $reason => $data)
            <div class="summary-item">
                <span>{{ ucfirst(str_replace('_', ' ', $reason)) }}:</span>
                <span>{{ number_format($data['cost'], 2) }} ({{ $data['count'] }} incidents)</span>
            </div>
        @endforeach
        <div class="summary-item">
            <span>Total Waste Cost:</span>
            <span>{{ number_format($totalWasteCost, 2) }}</span>
        </div>
    </div>

    @if(isset($wasteBySection) && count($wasteBySection) > 0)
        <h2>Waste by Section</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Section</th>
                    <th class="text-right">Incidents</th>
                    <th class="text-right">Total Cost</th>
                    <th class="text-right">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wasteBySection as $sectionName => $data)
                    <tr>
                        <td>{{ $sectionName }}</td>
                        <td class="text-right">{{ $data['count'] }}</td>
                        <td class="text-right">{{ number_format($data['cost'], 2) }}</td>
                        <td class="text-right">{{ number_format(($data['cost'] / max($totalWasteCost, 1)) * 100, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection