@extends('emails.layout')

@section('title', 'Low Stock Alert')
@section('header-title', '⚠️ Low Stock Alert')
@section('header-subtitle', 'Inventory Level Below Minimum')

@section('content')
    <div class="alert-box alert-warning">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">
            {{ $material->name }} is running low!
        </h2>
        <p style="margin: 0;">
            The current stock level has fallen below the minimum threshold. Immediate action may be required.
        </p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Material Name:</div>
            <div class="info-value"><strong>{{ $material->name }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Category:</div>
            <div class="info-value">{{ $material->category }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Current Stock:</div>
            <div class="info-value">
                <strong style="color: #dc3545;">{{ number_format($currentStock, 2) }} {{ $material->unit }}</strong>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Minimum Quantity:</div>
            <div class="info-value">{{ number_format($material->min_quantity, 2) }} {{ $material->unit }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Reorder Quantity:</div>
            <div class="info-value">{{ number_format($material->reorder_quantity, 2) }} {{ $material->unit }}</div>
        </div>
        @if($material->preferredSupplier)
            <div class="info-row">
                <div class="info-label">Preferred Supplier:</div>
                <div class="info-value">{{ $material->preferredSupplier->name }}</div>
            </div>
        @endif
    </div>

    <div style="margin: 20px 0;">
        <p style="margin-bottom: 5px; font-weight: 600;">Stock Level:</p>
        <div class="progress-bar">
            <div class="progress-fill {{ $stockPercentage <= 50 ? 'danger' : 'warning' }}"
                style="width: {{ min($stockPercentage, 100) }}%;">
            </div>
        </div>
        <p style="margin-top: 5px; font-size: 14px; color: #666;">
            {{ number_format($stockPercentage, 1) }}% of minimum quantity
        </p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}/inventory/{{ $material->id }}" class="button button-primary">
            View Material Details
        </a>
        <a href="{{ config('app.url') }}/procurements/create?material={{ $material->id }}" class="button button-success">
            Create Procurement Order
        </a>
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #666;">
            <strong>Recommended Action:</strong>
            @if($currentStock <= 0)
                Material is out of stock. Create an urgent procurement order for
                {{ number_format($material->reorder_quantity, 2) }} {{ $material->unit }}.
            @else
                Reorder {{ number_format($material->reorder_quantity, 2) }} {{ $material->unit }} to maintain adequate stock
                levels.
            @endif
        </p>
    </div>
@endsection