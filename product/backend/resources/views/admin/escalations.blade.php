@extends('admin.layout')

@section('title', 'Aprilo AI Escalations')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">Human review</div>
            <h1>Escalations</h1>
            <p class="help">Sensitive or unresolved questions move here for HR review instead of receiving an unsafe automated answer.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Question</th>
                <th>Employee / User</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($escalations as $escalation)
                <tr>
                    <td style="max-width: 320px;">
                        <div style="font-weight: 600; line-height: 1.4;">{{ $escalation->question?->question_text ?? 'N/A' }}</div>
                        <div style="font-size: 11px; color: var(--muted); margin-top: 5px;">
                            Asked on {{ $escalation->created_at ? $escalation->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                    </td>
                    <td>
                        @if ($escalation->question?->user)
                            <div style="font-weight: 600;">{{ $escalation->question->user->name }}</div>
                            <div style="font-size: 12px; color: var(--muted);">{{ $escalation->question->user->email }}</div>
                        @else
                            <div style="color: var(--muted); font-style: italic;">Public Visitor</div>
                        @endif
                    </td>
                    <td>
                        <span class="pill" style="background: rgba(227,38,38,0.1); color: var(--danger); border: 1px solid rgba(227,38,38,0.15);">
                            {{ str_replace('_', ' ', $escalation->category) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusStyle = match($escalation->status) {
                                'open' => 'background: rgba(241, 196, 15, 0.15); color: #b78a05; border: 1px solid rgba(241, 196, 15, 0.3);',
                                'in_review' => 'background: rgba(52, 152, 219, 0.15); color: #2980b9; border: 1px solid rgba(52, 152, 219, 0.3);',
                                'resolved' => 'background: rgba(46, 204, 113, 0.15); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.3);',
                                'closed' => 'background: rgba(149, 165, 166, 0.15); color: #7f8c8d; border: 1px solid rgba(149, 165, 166, 0.3);',
                                default => 'background: #eef1ea; color: #34413c;'
                            };
                        @endphp
                        <span class="pill" style="{{ $statusStyle }} font-size: 11px; text-transform: uppercase;">{{ $escalation->status }}</span>
                    </td>
                    <td>
                        <button class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: transparent; color: var(--green); border: 1px solid var(--green); cursor: pointer;" onclick="toggleEscalationDetails('{{ $escalation->id }}')">
                            Manage
                        </button>
                    </td>
                </tr>
                
                <!-- Collapsible Details Row -->
                <tr id="details-{{ $escalation->id }}" style="display: none; background: #faf9f6;">
                    <td colspan="5" style="padding: 20px; border-left: 4px solid var(--green); border-bottom: 1px solid var(--line);">
                        <form method="POST" action="{{ route('admin.escalations.update', $escalation) }}">
                            @csrf
                            @method('PATCH')
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                                <label class="field">
                                    Assigned HR Owner
                                    <select name="assigned_to" style="background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 8px;">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($hrUsers as $user)
                                            <option value="{{ $user->id }}" @selected($escalation->assigned_to === $user->id)>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="field">
                                    Resolution Status
                                    <select name="status" required style="background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 8px;">
                                        <option value="open" @selected($escalation->status === 'open')>Open / New</option>
                                        <option value="in_review" @selected($escalation->status === 'in_review')>In Review</option>
                                        <option value="resolved" @selected($escalation->status === 'resolved')>Resolved</option>
                                        <option value="closed" @selected($escalation->status === 'closed')>Closed</option>
                                    </select>
                                </label>
                            </div>
                            
                            <label class="field" style="margin-bottom: 16px;">
                                Resolution Notes / Actions Taken
                                <textarea name="resolution_note" rows="3" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--line); outline: none; font-family: inherit; resize: vertical;" placeholder="Detail the actions taken or notes to resolve this ticket...">{{ $escalation->resolution_note }}</textarea>
                            </label>
                            
                            @if($escalation->resolved_at)
                                <div style="font-size: 12px; color: var(--muted); margin-bottom: 12px;">
                                    ✓ Resolved on: {{ \Carbon\Carbon::parse($escalation->resolved_at)->format('M d, Y h:i A') }}
                                </div>
                            @endif

                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button class="button" type="submit" style="width: auto; padding: 8px 16px; border-radius: 6px;">Save Changes</button>
                                <button class="button" type="button" style="width: auto; padding: 8px 16px; background: transparent; border: 1px solid var(--line); color: var(--ink); border-radius: 6px;" onclick="toggleEscalationDetails('{{ $escalation->id }}')">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No escalations yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        function toggleEscalationDetails(id) {
            const row = document.getElementById('details-' + id);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>
@endsection
