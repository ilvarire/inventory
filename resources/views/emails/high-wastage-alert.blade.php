@extends('emails.layout')

@section('title', 'High Wastage Alert')
@section('header-title', '🚨 High Wastage Alert')
@section('header-subtitle', 'Wastage Threshold Exceeded')

@section('content')
    <div class="alert-box alert-danger">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">
            {{ $section->name }} has exceeded the wastage threshold!
        </h2>
        <p style="margin: 0;">
            The wastage level in this section requires immediate attention and investigation.
        </p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Section:</div>
            <div class="info-value"><strong>{{ $section->name }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Wastage Threshold:</div>
            <div class="info-value">{{ number_format($threshold, 2) }}%</div>
        </div>
        <div class="info-row">
            <div class="info-label">Actual Wastage:</div>
            <div class="info-value">
                <strong style="color: #dc3545;">{{ number_format($actualWastage, 2) }}%</strong>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Exceedance:</div>
            <div class="info-value">
                <strong>{{ number_format($exceedancePercentage, 1) }}% over threshold</strong>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Cost Impact:</div>
            <div class="info-value">
                <strong style="color: #dc3545; font-size: 18px;">${{ number_format($costImpact, 2) }}</strong>
            </div>
        </div>
    </div>

    <div style="margin: 20px 0;">
        <p style="margin-bottom: 5px; font-weight: 600;">Wastage Level:</p>
        <div class="progress-bar">
            <div class="progress-fill danger" style="width: {{ min(($actualWastage / max($threshold, 1)) * 100, 100) }}%;">
            </div>
        </div>
        <p style="margin-top: 5px; font-size: 14px; color: #666;">
            {{ number_format(($actualWastage / max($threshold, 1)) * 100, 1) }}% of threshold
        </p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}/reports/waste?section={{ $section->id }}" class="button button-primary">
            View Waste Report
        </a>
        <a href="{{ config('app.url') }}/sections/{{ $section->id }}/dashboard" class="button button-danger">
            Section Dashboard
        </a>
    </div>

    <div
        style="margin-top: 30px; padding: 15px; background-color: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
        <p style="margin: 0 0 10px 0; font-weight: 600; color: #856404;">
            ⚠️ Immediate Actions Required:
        </p>
        <ul style="margin: 0; padding-left: 20px; color: #856404;">
            <li>Review recent waste logs for this section</li>
            <li>Investigate root causes of excessive wastage</li>
            <li>Implement corrective measures</li>
            <li>Train staff on waste reduction practices</li>
            <li>Monitor wastage levels closely</li>
        </ul>
    </div>

    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #666;">
            <strong>Note:</strong> High wastage can significantly impact profitability.
            Please review the detailed waste report and take appropriate action to reduce waste in this section.
        </p>
    </div>
@endsection