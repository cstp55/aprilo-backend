@extends('admin.layout')

@section('title', 'Super Admin - Global Audit Logs')

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.dashboard') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Global Audit</div>
            <h1>Cross-Tenant Interaction Logs</h1>
            <p class="help">Unified trace of all employee queries, AI answers, and confidence ratings across every tenant organization.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Organization</th>
                <th>Question</th>
                <th>Employee</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>
                        <strong>{{ $log->organization->name ?? 'Demo Company' }}</strong>
                    </td>
                    <td style="max-width: 360px;">
                        <div style="font-weight: 600;">{{ $log->question_text }}</div>
                    </td>
                    <td>{{ $log->user->email ?? 'Visitor' }}</td>
                    <td>
                        <span class="pill" style="font-size: 11px; text-transform: uppercase;">{{ $log->status }}</span>
                    </td>
                    <td>{{ $log->created_at ? $log->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No interaction logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 18px;">
        {{ $logs->links() }}
    </div>
@endsection
