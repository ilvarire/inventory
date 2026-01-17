@extends('emails.layout')

@section('title', 'Approval Status Update')
@section('header-title', $isApproved ? '✅ Request Approved' : '❌ Request Rejected')
@section('header-subtitle', 'Status Update')

@section('content')
    <div class="alert-box {{ $isApproved ? 'alert-success' : 'alert-danger' }}">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">
            Your {{ $typeLabel }} has been {{ $status }}
        </h2>
        <p style="margin: 0;">
            {{ $approver->name }} has {{ $status }} your {{ $typeLabel }} request.
        </p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Request Type:</div>
            <div class="info-value"><strong>{{ $typeLabel }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Request ID:</div>
            <div class="info-value">#{{ $id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
                <span class="badge badge-{{ $isApproved ? 'low' : 'critical' }}">
                    {{ strtoupper($status) }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ $isApproved ? 'Approved' : 'Rejected' }} By:</div>
            <div class="info-value">{{ $approver->name }} ({{ $approver->role->name }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date:</div>
            <div class="info-value">{{ now()->format('F d, Y \a\t h:i A') }}</div>
        </div>
    </div>

    @if($notes)
        <div
            style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 6px; border-left: 4px solid {{ $isApproved ? '#28a745' : '#dc3545' }};">
            <p style="margin: 0 0 10px 0; font-weight: 600;">{{ $isApproved ? 'Approval' : 'Rejection' }} Notes:</p>
            <p style="margin: 0; font-style: italic; color: #666;">
                "{{ $notes }}"
            </p>
        </div>
    @endif

    @if($isApproved)
        <div style="margin: 20px 0; padding: 15px; background-color: #d4edda; border-radius: 6px;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #155724;">
                ✅ Next Steps:
            </p>
            <ul style="margin: 0; padding-left: 20px; color: #155724;">
                @if($type === 'material_request')
                    <li>Your material request will be fulfilled by the store keeper</li>
                    <li>You will be notified when materials are ready for collection</li>
                @elseif($type === 'production')
                    <li>Your production log has been approved</li>
                    <li>The produced items are now available in inventory</li>
                @elseif($type === 'waste')
                    <li>Your waste report has been approved</li>
                    <li>The waste has been recorded in the system</li>
                @else
                    <li>Your request has been processed successfully</li>
                @endif
            </ul>
        </div>
    @else
        <div style="margin: 20px 0; padding: 15px; background-color: #f8d7da; border-radius: 6px;">
            <p style="margin: 0; font-weight: 600; color: #721c24;">
                ❌ What This Means:
            </p>
            <p style="margin: 10px 0 0 0; color: #721c24;">
                Your request has not been approved. Please review the rejection notes above and contact {{ $approver->name }} if
                you have questions or need to resubmit with modifications.
            </p>
        </div>
    @endif

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}/{{ str_replace('_', '-', $type) }}/{{ $id }}" class="button button-primary">
            View Request Details
        </a>
    </div>
@endsection