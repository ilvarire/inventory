@extends('exports.pdf.layout')

@section('title', 'Profit & Loss Statement')
@section('report-title', 'Profit & Loss Statement')

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
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
                <th class="text-right">% of Revenue</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #d5f4e6;">
                <td><strong>REVENUE</strong></td>
                <td class="text-right"><strong>{{ number_format($data['revenue'], 2) }}</strong></td>
                <td class="text-right"><strong>100.00%</strong></td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr style="background-color: #ffe6e6;">
                <td><strong>COST OF SALES</strong></td>
                <td class="text-right"></td>
                <td class="text-right"></td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Cost of Goods Sold</td>
                <td class="text-right">{{ number_format($data['cost_of_sales'], 2) }}</td>
                <td class="text-right">{{ number_format(($data['cost_of_sales'] / max($data['revenue'], 1)) * 100, 2) }}%
                </td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr style="background-color: #e3f2fd;">
                <td><strong>GROSS PROFIT</strong></td>
                <td class="text-right"><strong>{{ number_format($grossProfit, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($grossMargin, 2) }}%</strong></td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr style="background-color: #fff3cd;">
                <td><strong>OPERATING EXPENSES</strong></td>
                <td class="text-right"></td>
                <td class="text-right"></td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Total Expenses</td>
                <td class="text-right">{{ number_format($data['expenses'], 2) }}</td>
                <td class="text-right">{{ number_format(($data['expenses'] / max($data['revenue'], 1)) * 100, 2) }}%</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Waste Cost</td>
                <td class="text-right">{{ number_format($data['waste'], 2) }}</td>
                <td class="text-right">{{ number_format(($data['waste'] / max($data['revenue'], 1)) * 100, 2) }}%</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="padding-left: 20px;">Total Operating Expenses</td>
                <td class="text-right">{{ number_format($data['expenses'] + $data['waste'], 2) }}</td>
                <td class="text-right">
                    {{ number_format((($data['expenses'] + $data['waste']) / max($data['revenue'], 1)) * 100, 2) }}%</td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr style="background-color: {{ $netProfit >= 0 ? '#d5f4e6' : '#ffe6e6' }}; font-size: 11pt;">
                <td><strong>NET PROFIT</strong></td>
                <td class="text-right"><strong>{{ number_format($netProfit, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($netMargin, 2) }}%</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Key Metrics</h3>
        <div class="summary-item">
            <span>Gross Profit Margin:</span>
            <span
                class="{{ $grossMargin >= 50 ? 'badge badge-success' : 'badge badge-warning' }}">{{ number_format($grossMargin, 2) }}%</span>
        </div>
        <div class="summary-item">
            <span>Net Profit Margin:</span>
            <span
                class="{{ $netMargin >= 20 ? 'badge badge-success' : ($netMargin >= 10 ? 'badge badge-warning' : 'badge badge-danger') }}">{{ number_format($netMargin, 2) }}%</span>
        </div>
        <div class="summary-item">
            <span>Operating Expense Ratio:</span>
            <span>{{ number_format((($data['expenses'] + $data['waste']) / max($data['revenue'], 1)) * 100, 2) }}%</span>
        </div>
    </div>
@endsection