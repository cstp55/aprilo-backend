@extends('admin.layout')

@section('title', 'Super Admin - Organizations Directory')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Platform Directory</div>
            <h1>All Registered Organizations</h1>
            <p class="help">Detailed inspection of client tenant settings, user rosters, and service allocations.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Organization</th>
                <th>Users Count</th>
                <th>Knowledge Chunks</th>
                <th>Questions Count</th>
                <th>Billing Plan</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organizations as $org)
                <tr>
                    <td>
                        <strong>{{ $org->name }}</strong>
                        <div style="font-size: 11px; color: var(--muted);">UUID: {{ $org->id }}</div>
                    </td>
                    <td>{{ $org->users_count }} users</td>
                    <td>{{ $org->knowledge_sources_count }} sources</td>
                    <td>{{ $org->questions_count }} queries</td>
                    <td>
                        <span class="pill" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; font-weight: 700; text-transform: uppercase;">
                            {{ $org->plan ?? 'Enterprise' }}
                        </span>
                    </td>
                    <td>{{ $org->created_at ? $org->created_at->format('M d, Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No organizations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 18px;">
        {{ $organizations->links() }}
    </div>
@endsection
