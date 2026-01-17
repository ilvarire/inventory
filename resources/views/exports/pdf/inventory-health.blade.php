@extends('exports.pdf.layout')

@section('title', 'Inventory Health Report')
@section('report-title', 'Inventory Health Report')

@section('content')
    <div class="report-info">
        <table>
            <tr>
                <td>Report Date:</td>
                <td>{{ date('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Total Materials:</td>
                <td>{{ $materials->count() }}</td>
            </tr>
            <tr>
                <td>Low Stock Items:</td>
                <td>{{ $lowStockCount }}</td>
            </tr>
            <tr>
                <td>Out of Stock Items:</td>
                <td>{{ $outOfStockCount }}</td>
            </tr>
        </table>
    </div>

    <h2>Inventory Status</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Material</th>
                <th>Category</th>
                <th class="text-right">Current Stock</th>
                <th class="text-right">Min Qty</th>
                <th class="text-right">Reorder Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $material)
                @php
                    $status = 'OK';
                    $badgeClass = 'badge-success';

                    if ($material['current_stock'] <= 0) {
                        $status = 'OUT OF STOCK';
                        $badgeClass = 'badge-danger';
                    } elseif ($material['current_stock'] <= $material['min_quantity']) {
                        $status = 'LOW STOCK';
                        $badgeClass = 'badge-warning';
                    } elseif ($material['current_stock'] >= $material['reorder_quantity'] * 2) {
                        $status = 'OVERSTOCKED';
                        $badgeClass = 'badge-info';
                    }
                @endphp
                <tr>
                    <td>{{ $material['name'] }}</td>
                    <td>{{ $material['category'] }}</td>
                    <td class="text-right">{{ number_format($material['current_stock'], 2) }} {{ $material['unit'] }}</td>
                    <td class="text-right">{{ number_format($material['min_quantity'], 2) }}</td>
                    <td class="text-right">{{ number_format($material['reorder_quantity'], 2) }}</td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($lowStockItems->count() > 0)
        <div class="summary-box" style="background-color: #fff3cd; border-left-color: #f39c12;">
            <h3>⚠️ Action Required - Low Stock Items</h3>
            @foreach($lowStockItems as $item)
                <div class="summary-item">
                    <span>{{ $item['name'] }}:</span>
                    <span>{{ number_format($item['current_stock'], 2) }} {{ $item['unit'] }} (Min:
                        {{ number_format($item['min_quantity'], 2) }})</span>
                </div>
            @endforeach
        </div>
    @endif
@endsection