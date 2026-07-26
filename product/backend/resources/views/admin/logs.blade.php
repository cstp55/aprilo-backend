@extends('admin.layout')

@section('title', 'Aprilo AI Interaction Logs')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">Audit & Analytics</div>
            <h1>Interaction Logs</h1>
            <p class="help">View history of employee questions, AI answers, source citations, confidence scores, and employee rating feedback.</p>
        </div>
    </div>

    <!-- Filter Panel -->
    <form class="card" method="GET" action="{{ route('admin.logs') }}" style="margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
            <label class="field">
                Sensitivity Status
                <select name="sensitivity_status" style="background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 8px;">
                    <option value="">All Sensitivity</option>
                    <option value="normal" @selected(request('sensitivity_status') === 'normal')>Normal</option>
                    <option value="sensitive" @selected(request('sensitivity_status') === 'sensitive')>Sensitive Topic</option>
                </select>
            </label>
            <label class="field">
                Answer Status
                <select name="status" style="background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 8px;">
                    <option value="">All Statuses</option>
                    <option value="answered" @selected(request('status') === 'answered')>Answered</option>
                    <option value="fallback" @selected(request('status') === 'fallback')>Fallback</option>
                    <option value="escalated" @selected(request('status') === 'escalated')>Escalated to HR</option>
                </select>
            </label>
            <div style="display: flex; gap: 8px;">
                <button class="button" type="submit" style="width: auto; padding: 10px 20px; border-radius: 6px;">Apply Filters</button>
                <a href="{{ route('admin.logs') }}" class="button" style="width: auto; padding: 10px 20px; background: transparent; border: 1px solid var(--line); color: var(--ink); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">Reset</a>
            </div>
        </div>
    </form>

    <!-- Logs Table -->
    <table>
        <thead>
            <tr>
                <th>Question</th>
                <th>Employee / User</th>
                <th>Sensitivity</th>
                <th>Answer Status</th>
                <th>Feedback Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td style="max-width: 320px;">
                        <div style="font-weight: 600; line-height: 1.4;">{{ $log->question_text }}</div>
                        <div style="font-size: 11px; color: var(--muted); margin-top: 5px;">
                            Asked: {{ $log->created_at ? $log->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                    </td>
                    <td>
                        @if ($log->user)
                            <div style="font-weight: 600;">{{ $log->user->name }}</div>
                            <div style="font-size: 12px; color: var(--muted);">{{ $log->user->email }}</div>
                        @else
                            <div style="color: var(--muted); font-style: italic;">Public Visitor</div>
                        @endif
                    </td>
                    <td>
                        @if ($log->sensitivity_status === 'sensitive')
                            <span class="pill" style="background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); font-size: 11px; text-transform: uppercase;">Sensitive</span>
                        @else
                            <span class="pill" style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); font-size: 11px; text-transform: uppercase;">Normal</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $statusStyle = match($log->status) {
                                'answered' => 'background: rgba(46, 204, 113, 0.15); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.3);',
                                'fallback' => 'background: rgba(241, 196, 15, 0.15); color: #d35400; border: 1px solid rgba(241, 196, 15, 0.3);',
                                'escalated' => 'background: rgba(155, 89, 182, 0.15); color: #8e44ad; border: 1px solid rgba(155, 89, 182, 0.3);',
                                default => 'background: #eef1ea; color: #34413c;'
                            };
                        @endphp
                        <span class="pill" style="{{ $statusStyle }} font-size: 11px; text-transform: uppercase;">{{ $log->status }}</span>
                    </td>
                    <td>
                        @php
                            $answer = $log->answers->first();
                            $feedback = $answer ? $answer->feedback->first() : null;
                        @endphp
                        @if ($feedback)
                            @if ($feedback->rating === 'helpful')
                                <span class="pill" style="background: rgba(46, 204, 113, 0.2); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.4); font-size: 11px;">👍 Helpful</span>
                            @else
                                <span class="pill" style="background: rgba(231, 76, 60, 0.2); color: #c0392b; border: 1px solid rgba(231, 76, 60, 0.4); font-size: 11px;">👎 Not Helpful</span>
                            @endif
                        @else
                            <span style="font-size: 12px; color: var(--muted); font-style: italic;">No rating</span>
                        @endif
                    </td>
                    <td>
                        <button class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: transparent; color: var(--green); border: 1px solid var(--green); cursor: pointer;" onclick="toggleLogDetails('{{ $log->id }}')">
                            Inspect
                        </button>
                    </td>
                </tr>

                <!-- Collapsible Log Details Row -->
                <tr id="log-details-{{ $log->id }}" style="display: none; background: #faf9f6;">
                    <td colspan="6" style="padding: 20px; border-left: 4px solid var(--green); border-bottom: 1px solid var(--line);">
                        @if ($answer)
                            <div style="margin-bottom: 16px;">
                                <h3 style="margin: 0 0 8px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted);">AI Generated Answer</h3>
                                <div style="background: #fff; border: 1px solid var(--line); padding: 12px; border-radius: 6px; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $answer->answer_text }}</div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                                <div>
                                    <h3 style="margin: 0 0 8px 0; font-size: 13px; text-transform: uppercase; color: var(--muted);">Model Information</h3>
                                    <div style="font-size: 13px;">
                                        <strong>Provider:</strong> {{ $answer->model_provider ?? 'Gemini' }}<br>
                                        <strong>Model Name:</strong> {{ $answer->model_name ?? 'gemini-flash-latest' }}<br>
                                        <strong>Confidence Label:</strong> 
                                        @php
                                            $confColor = match($answer->confidence_label) {
                                                'high' => '#27ae60',
                                                'medium' => '#f39c12',
                                                'low' => '#c0392b',
                                                default => 'inherit'
                                            };
                                        @endphp
                                        <span style="color: {{ $confColor }}; font-weight: 700; text-transform: uppercase;">{{ $answer->confidence_label ?? 'LOW' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <h3 style="margin: 0 0 8px 0; font-size: 13px; text-transform: uppercase; color: var(--muted);">Employee Feedback Comment</h3>
                                    @if ($feedback && $feedback->comment)
                                        <div style="background: #fff; border: 1px solid rgba(231,76,60,0.15); padding: 8px 12px; border-radius: 6px; font-size: 13px; font-style: italic; color: #555;">
                                            "{{ $feedback->comment }}"
                                        </div>
                                    @else
                                        <div style="font-size: 13px; color: var(--muted); font-style: italic;">No comment provided.</div>
                                    @endif
                                </div>
                            </div>

                            @if ($answer->sources->isNotEmpty())
                                <div>
                                    <h3 style="margin: 0 0 8px 0; font-size: 13px; text-transform: uppercase; color: var(--muted);">Retrieved Citations & Excerpts</h3>
                                    <div style="display: grid; gap: 8px;">
                                        @foreach ($answer->sources as $src)
                                            <div style="background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 10px; font-size: 13px;">
                                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                                    <strong>{{ $src->citation_label }} {{ $src->source->title ?? 'Unknown' }}</strong>
                                                    @if (isset($src->score))
                                                        <span style="font-size: 11px; font-family: monospace; color: var(--muted);">Similarity Score: {{ $src->score }}</span>
                                                    @endif
                                                </div>
                                                <div style="color: #444; font-size: 12.5px; line-height: 1.5; font-style: italic;">
                                                    "{{ $src->chunk->chunk_text ?? '' }}"
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <div style="color: var(--muted); font-style: italic; font-size: 14px;">No answer generated for this question (or request failed).</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No interaction logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination Controls -->
    @if ($logs->hasPages())
        <div style="margin-top: 18px; display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 12px; border: 1px solid var(--line); border-radius: 8px;">
            <div style="font-size: 13px; color: var(--muted);">
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} interactions
            </div>
            <div style="display: flex; gap: 6px;">
                @if ($logs->onFirstPage())
                    <span style="padding: 6px 12px; border: 1px solid var(--line); border-radius: 4px; color: var(--muted); cursor: not-allowed; font-size: 13px; background: #faf9f6;">Previous</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--line); border-radius: 4px; background: var(--surface); font-size: 13px; font-weight: 600; text-decoration: none; color: inherit;">Previous</a>
                @endif

                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--line); border-radius: 4px; background: var(--surface); font-size: 13px; font-weight: 600; text-decoration: none; color: inherit;">Next</a>
                @else
                    <span style="padding: 6px 12px; border: 1px solid var(--line); border-radius: 4px; color: var(--muted); cursor: not-allowed; font-size: 13px; background: #faf9f6;">Next</span>
                @endif
            </div>
        </div>
    @endif

    <script>
        function toggleLogDetails(id) {
            const row = document.getElementById('log-details-' + id);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>
@endsection
