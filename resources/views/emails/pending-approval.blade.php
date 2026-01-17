@extends('emails.layout')

@section('title', 'Pending Approval')
@section('header-title', '📋 Pending Approval')
@section('header-subtitle', 'Action Required')

@section('content')
    <div class="alert-box alert-info">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">
            New {{ $typeLabel }} requires your approval
        </h2>
        <p style="margin: 0;">
            {{ $requester->name }} has submitted a {{ $typeLabel }} that needs your review and approval.
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
            <div class="info-label">Requested By:</div>
            <div class="info-value">{{ $requester->name }} ({{ $requester->role->name }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Submitted:</div>
            <div class="info-value">{{ now()->format('F d, Y \a\t h:i A') }}</div>
        </div>
        @if($requester->section)
            <div class="info-row">
                <div class="info-label">Section:</div>
                <div class="info-value">{{ $requester->section->name }}</div>
            </div>
        @endif
    </div>

    @if(!empty($details))
        <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
            <p style="margin: 0 0 10px 0; font-weight: 600;">Request Details:</p>
            @foreach($details as $key => $value)
                <p style="margin: 5px 0; font-size: 14px;">
                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}
                </p>
            @endforeach
        </div>
    @endif

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ $approvalUrl }}" class="button button-primary">
            Review Request
        </a>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <p style="font-size: 14px; color: #666;">
            Or use the quick actions below:
        </p>
        <a href="{{ $approvalUrl }}/approve" class="button button-success">
            ✓ Approve
        </a>
        <a href="{{ $approvalUrl }}/reject" class="button button-danger">
            ✗ Reject
        </a>
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #666;">
            <strong>Note:</strong> Please review the request details carefully before making a decision.
            You can add notes or comments when approving or rejecting the request.
        </p>
    </div>
@endsection