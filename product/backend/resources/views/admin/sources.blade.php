@extends('admin.layout')

@section('title', 'Aprilo AI Sources')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">Knowledge operations</div>
            <h1>Sources</h1>
            <p class="help">Upload approved HR knowledge. Indexed sources are used by the customer-facing Aprilo AI question experience.</p>
        </div>
    </div>

    <form class="card" method="POST" action="{{ route('admin.sources.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-2">
            <label class="field">
                Source title
                <input name="title" value="{{ old('title', 'Employee Handbook') }}" required>
            </label>
            <label class="field">
                Access scope
                <select name="access_scope" required>
                    <option value="all_employees">All employees</option>
                    <option value="hr_only">HR only</option>
                </select>
            </label>
            <label class="field">
                Source file
                <input name="file" type="file" required>
            </label>
            <div style="display:flex;align-items:end;">
                <button class="button" type="submit">Upload and index</button>
            </div>
        </div>
    </form>

    <div style="margin-top:18px;">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Chunks</th>
                    <th>Scope</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sources as $source)
                    @php
                        $pillStyle = match($source->status) {
                            'indexed' => 'background: rgba(46, 204, 113, 0.15); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                            'failed' => 'background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                            'indexing', 'uploaded' => 'background: rgba(241, 196, 15, 0.15); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                            'inactive' => 'background: rgba(149, 165, 166, 0.15); color: #7f8c8d; border: 1px solid rgba(149, 165, 166, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                            default => 'background: rgba(149, 165, 166, 0.15); color: #7f8c8d; padding: 2px 8px; border-radius: 99px; font-size: 11px;'
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $source->title }}</strong>
                            @if (!empty($source->metadata['version']) && $source->metadata['version'] > 1)
                                <span style="font-size: 10px; opacity: 0.6; margin-left: 4px;">(v{{ $source->metadata['version'] }})</span>
                            @endif
                        </td>
                        <td><span style="font-family: monospace; font-size: 12px; text-transform: uppercase; background: #eef1ea; padding: 2px 6px; border-radius: 4px;">{{ $source->source_type }}</span></td>
                        <td>
                            <span class="pill" style="{{ $pillStyle }}">{{ $source->status }}</span>
                            @if ($source->status === 'failed' && !empty($source->metadata['indexing_error']))
                                <div style="font-size: 11px; color: #e74c3c; margin-top: 5px; max-width: 280px; white-space: normal; line-height: 1.4; font-weight: 500;">
                                    ⚠️ {{ $source->metadata['indexing_error'] }}
                                </div>
                            @endif
                        </td>
                        <td style="font-weight: 600;">{{ $source->chunks_count }}</td>
                        <td>
                            <span style="font-size: 12px; font-weight: 600; text-transform: capitalize; color: #58625f;">
                                {{ str_replace('_', ' ', $source->access_scope) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No sources uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
