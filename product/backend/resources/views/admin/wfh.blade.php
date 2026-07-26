@extends('admin.layout')

@section('title', 'WFH Requests - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">HR Operations</div>
            <h1>WFH Requests</h1>
            <p class="help">Review employee Work From Home requests and approve or reject pending requests.</p>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        @if ($wfhRequests->isEmpty())
            <div style="padding: 30px; text-align: center; color: var(--muted);">
                <p>No WFH requests found.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Requested Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wfhRequests as $request)
                        <tr>
                            <td>
                                <strong>{{ $request->user->name }}</strong>
                                <div style="font-size: 11px; color: var(--muted);">{{ $request->user->email }} (ID: {{ $request->user->employee_id ?: 'N/A' }})</div>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($request->date)->format('F d, Y') }}</div>
                            </td>
                            <td>
                                <div style="max-width: 350px;">{{ $request->reason ?: 'N/A' }}</div>
                            </td>
                            <td>
                                @if ($request->status === 'approved')
                                    <span class="pill" style="background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8;">Approved</span>
                                @elseif ($request->status === 'rejected')
                                    <span class="pill" style="background: #fdf2f2; color: #b82c2c; border: 1px solid #fbdad9;">Rejected</span>
                                @else
                                    <span class="pill" style="background: #fef8eb; color: #b27300; border: 1px solid #fdf0cc;">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if ($request->approver)
                                    <span>{{ $request->approver->name }}</span>
                                @else
                                    <span style="color: var(--muted); font-style: italic;">Unreviewed</span>
                                @endif
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                @if ($request->status === 'pending')
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <form method="POST" action="{{ route('admin.wfh.update', $request->id) }}" style="margin: 0; padding: 0; width: auto; border: 0; box-shadow: none;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" style="background: #1f7a3f; font-size: 12px; padding: 6px 12px; margin: 0; width: auto;">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.wfh.update', $request->id) }}" style="margin: 0; padding: 0; width: auto; border: 0; box-shadow: none;">
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
