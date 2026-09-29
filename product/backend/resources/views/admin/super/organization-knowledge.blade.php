@extends('admin.layout')

@section('title', 'Knowledge Base Support - ' . $organization->name)

@section('content')
    <div class="header" style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Super-admin support</div>
            <h1>Knowledge base · {{ $organization->name }}</h1>
            <p class="help">Upload and review the sources used by this organization's AI widget.</p>
        </div>
        <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.show', $organization) }}">Organization details</a>
    </div>

    <form class="card" method="POST" action="{{ route('admin.super.organizations.knowledge.store', $organization) }}" enctype="multipart/form-data" style="margin-bottom: 20px;">
        @csrf
        <h2 style="font-size: 18px; margin: 0 0 16px;">Upload a source for {{ $organization->name }}</h2>
        <div class="grid grid-3">
            <label class="field">Source title
                <input name="title" value="{{ old('title') }}" required maxlength="255">
            </label>
            <label class="field">Access scope
                <select name="access_scope" required>
                    <option value="all_employees">Public / customer-visible</option>
                    <option value="hr_only">Internal only</option>
                </select>
            </label>
            <label class="field">Source file
                <input name="file" type="file" accept=".pdf,.docx,.txt,.md,.markdown" required>
            </label>
        </div>
        <button class="button" type="submit" style="width: auto; margin-top: 16px;">Upload and index</button>
    </form>

    <section>
        <h2 style="font-size: 18px; margin: 0 0 12px;">Sources · {{ $organization->name }}</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>File type</th>
                        <th>Access scope</th>
                        <th>Status</th>
                        <th>Chunks</th>
                        <th>Uploaded</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sources as $source)
                        <tr>
                            <td>
                                <strong>{{ $source->title }}</strong>
                                @if (!empty($source->metadata['original_name']))
                                    <div style="font-size: 11px; color: var(--muted);">{{ $source->metadata['original_name'] }}</div>
                                @endif
                            </td>
                            <td>{{ strtoupper($source->source_type) }}</td>
                            <td>{{ str_replace('_', ' ', $source->access_scope) }}</td>
                            <td><span class="pill">{{ $source->status }}</span></td>
                            <td>{{ $source->chunks_count }}</td>
                            <td>{{ $source->created_at?->format('M d, Y') ?: 'Unknown' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No knowledge sources have been uploaded for this organization.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
