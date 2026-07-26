@extends('admin.layout')

@section('title', 'Leave Requests - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">HR Operations</div>
            <h1>Leave Requests</h1>
            <p class="help">Review employee leave request history and approve or reject pending requests.</p>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        @if ($leaves->isEmpty())
            <div style="padding: 30px; text-align: center; color: var(--muted);">
                <p>No leave requests found.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Dates</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaves as $leave)
                        <tr>
                            <td>
                                <strong>{{ $leave->user->name }}</strong>
                                <div style="font-size: 11px; color: var(--muted);">{{ $leave->user->email }} (ID: {{ $leave->user->employee_id ?: 'N/A' }})</div>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</div>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}
                            </td>
                            <td>
                                <div style="max-width: 250px;">{{ $leave->reason ?: 'N/A' }}</div>
                            </td>
                            <td>
                                @if ($leave->status === 'approved')
                                    <span class="pill" style="background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8;">Approved</span>
                                @elseif ($leave->status === 'rejected')
                                    <span class="pill" style="background: #fdf2f2; color: #b82c2c; border: 1px solid #fbdad9;">Rejected</span>
                                @else
                                    <span class="pill" style="background: #fef8eb; color: #b27300; border: 1px solid #fdf0cc;">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if ($leave->approver)
                                    <span>{{ $leave->approver->name }}</span>
                                @else
                                    <span style="color: var(--muted); font-style: italic;">Unreviewed</span>
                                @endif
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                @if ($leave->status === 'pending')
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <form method="POST" action="{{ route('admin.leaves.update', $leave->id) }}" style="margin: 0; padding: 0; width: auto; border: 0; box-shadow: none;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" style="background: #1f7a3f; font-size: 12px; padding: 6px 12px; margin: 0; width: auto;">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.leaves.update', $leave->id) }}" style="margin: 0; padding: 0; width: auto; border: 0; box-shadow: none;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" style="background: #b82c2c; font-size: 12px; padding: 6px 12px; margin: 0; width: auto;">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span style="font-size: 12px; color: var(--muted);">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
