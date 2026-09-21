@extends('admin.layout')

@section('title', 'Super Admin - Global Audit Logs')

@section('content')
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
