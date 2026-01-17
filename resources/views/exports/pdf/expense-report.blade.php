@extends('exports.pdf.layout')

@section('title', 'Expense Report')
@section('report-title', 'Expense Report')

@section('content')
    <div class="report-info">
        <table>
            <tr>
                <td>Report Period:</td>
                <td>{{ $startDate }} to {{ $endDate }}</td>
            </tr>
            <tr>
                <td>Total Expenses:</td>
                <td>{{ $expenses->count() }}</td>
            </tr>
        </table>
    </div>

    <h2>Expense Details</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Section</th>
                <th>Type</th>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date }}</td>
                    <td>{{ $expense->section->name ?? 'General' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $expense->type)) }}</td>
                    <td>{{ $expense->description }}</td>
                    <td class="text-right">{{ number_format($expense->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Expense Summary by Type</h3>
        @foreach($expensesByType as $type => $amount)
            <div class="summary-item">
                <span>{{ ucfirst(str_replace('_', ' ', $type)) }}:</span>
                <span>{{ number_format($amount, 2) }}</span>
            </div>
        @endforeach
        <div class="summary-item">
            <span>Total Expenses:</span>
            <span>{{ number_format($totalExpenses, 2) }}</span>
        </div>
    </div>

    @if(isset($generalExpenses) && isset($sectionExpenses))
        <h2>Expense Breakdown</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">% of Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>General Expenses</td>
                    <td class="text-right">{{ number_format($generalExpenses, 2) }}</td>
                    <td class="text-right">{{ number_format(($generalExpenses / max($totalExpenses, 1)) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td>Section-Specific Expenses</td>
                    <td class="text-right">{{ number_format($sectionExpenses, 2) }}</td>
                    <td class="text-right">{{ number_format(($sectionExpenses / max($totalExpenses, 1)) * 100, 2) }}%</td>
                </tr>
            </tbody>
        </table>
    @endif
@endsection