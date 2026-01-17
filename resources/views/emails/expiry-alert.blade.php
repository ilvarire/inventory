@extends('emails.layout')

@section('title', 'Expiry Alert')
@section('header-title', '⏰ Expiry Alert')
@section('header-subtitle', 'Material Expiring Soon')

@section('content')
    <div
        class="alert-box {{ $urgency === 'critical' ? 'alert-danger' : ($urgency === 'high' ? 'alert-warning' : 'alert-info') }}">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">
            @if($urgency === 'critical')
                🚨 Critical: Material expiring in {{ $daysUntilExpiry }} {{ Str::plural('day', $daysUntilExpiry) }}!
            @elseif($urgency === 'high')
                ⚠️ Warning: Material expiring soon
            @else
                ℹ️ Notice: Upcoming expiry
            @endif
        </h2>
        <p style="margin: 0;">
            A batch of {{ $batch->rawMaterial->name }} will expire on {{ $batch->expiry_date->format('F d, Y') }}.
        </p>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <span class="badge badge-{{ $urgency === 'critical' ? 'critical' : ($urgency === 'high' ? 'high' : 'medium') }}">
            {{ $urgency }} Priority
        </span>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Material Name:</div>
            <div class="info-value"><strong>{{ $batch->rawMaterial->name }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Batch ID:</div>
            <div class="info-value">#{{ $batch->id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Expiry Date:</div>
            <div class="info-value">
                <strong style="color: {{ $urgency === 'critical' ? '#dc3545' : '#fd7e14' }};">
                    {{ $batch->expiry_date->format('F d, Y') }}
                </strong>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Days Until Expiry:</div>
            <div class="info-value">
                <strong>{{ $daysUntilExpiry }} {{ Str::plural('day', $daysUntilExpiry) }}</strong>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Remaining Quantity:</div>
            <div class="info-value">
                {{ number_format($remainingQuantity, 2) }} {{ $batch->rawMaterial->unit }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Unit Cost:</div>
            <div class="info-value">${{ number_format($batch->unit_cost, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Value at Risk:</div>
            <div class="info-value">
                <strong>${{ number_format($remainingQuantity * $batch->unit_cost, 2) }}</strong>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}/inventory/{{ $batch->raw_material_id }}" class="button button-primary">
            View Material Details
        </a>
        @if($urgency === 'critical')
            <a href="{{ config('app.url') }}/waste/create?batch={{ $batch->id }}" class="button button-danger">
                Report as Waste
            </a>
        @endif
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #666;">
            <strong>Recommended Action:</strong>
            @if($urgency === 'critical')
                Prioritize usage of this batch immediately or report as waste if unusable.
            @elseif($urgency === 'high')
                Plan to use this batch within the next few days to avoid waste.
            @else
                Monitor this batch and plan usage before expiry date.
            @endif
        </p>
    </div>
@endsection