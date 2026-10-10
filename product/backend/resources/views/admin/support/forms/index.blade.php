@extends('admin.layout')

@section('title', 'Aprilo Support - Dynamic Ticket Forms')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold" style="color: var(--green); text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px;">Aprilo Support Product</div>
            <h1 style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                Dynamic Ticket Forms
                <span class="pill" style="font-size: 11px; background: rgba(34, 197, 94, 0.1); color: var(--green); border: 1px solid rgba(34, 197, 94, 0.2); text-transform: uppercase; padding: 2px 8px; border-radius: 9999px;">
                    Forms Engine
                </span>
            </h1>
            <p class="help" style="color: var(--muted); margin-top: 4px; font-size: 14px;">
                Build custom customer-facing ticket intake forms with dynamic fields, branching conditions, validation rules, and live preview.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.support.settings') }}" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>⚙️</span> Portal Settings
            </a>
            <a href="{{ route('admin.support.forms.create') }}" class="button" style="display: inline-flex; align-items: center; gap: 6px;">
                <span>+</span> Build New Form
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-4" style="margin-top: 24px; margin-bottom: 24px;">
        <div class="card" style="background: #fff; padding: 18px;">
            <div class="metric" style="color: var(--ink); font-size: 26px;">{{ $forms->count() }}</div>
            <div class="label" style="font-size: 12px;">Total Forms</div>
        </div>
        <div class="card" style="background: #fff; padding: 18px;">
            <div class="metric" style="color: #16a34a; font-size: 26px;">{{ $forms->where('status', 'published')->count() }}</div>
            <div class="label" style="font-size: 12px;">Published Live</div>
        </div>
        <div class="card" style="background: #fff; padding: 18px;">
            <div class="metric" style="color: #f59e0b; font-size: 26px;">{{ $forms->where('status', 'draft')->count() }}</div>
            <div class="label" style="font-size: 12px;">In Draft</div>
        </div>
        <div class="card" style="background: #fff; padding: 18px;">
            <div class="metric" style="color: #64748b; font-size: 26px;">{{ $forms->where('status', 'archived')->count() }}</div>
            <div class="label" style="font-size: 12px;">Archived</div>
        </div>
    </div>

    <!-- Forms List Table -->
    <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Configured Forms</h3>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Each form provides custom intake fields and validates submissions without code deployment.</p>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--line); text-align: left; font-size: 11px; text-transform: uppercase; color: var(--muted);">
                        <th style="padding: 12px;">Form Details</th>
                        <th style="padding: 12px;">Associated Category</th>
                        <th style="padding: 12px;">Fields</th>
                        <th style="padding: 12px;">Status & Version</th>
                        <th style="padding: 12px;">Last Updated</th>
                        <th style="padding: 12px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $form)
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                            <td style="padding: 14px 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 14px;">{{ $form->name }}</span>
                                    @if ($form->is_default)
                                        <span class="pill" style="background: #dbeafe; color: #1d4ed8; font-size: 10px; padding: 2px 6px;">Default</span>
                                    @endif
                                </div>
                                @if ($form->description)
                                    <div style="font-size: 12px; color: var(--muted); margin-top: 3px; max-width: 320px;">{{ $form->description }}</div>
                                @endif
                                <div style="font-size: 11px; color: var(--muted); font-family: monospace; margin-top: 3px;">
                                    Slug: {{ $form->slug }}
                                </div>
                            </td>
                            <td style="padding: 14px 12px;">
                                @if ($form->category)
                                    <span class="pill" style="background: #f1f5f9; color: #334155;">{{ $form->category->name }}</span>
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ $form->category->department ?? 'General' }}</div>
                                @else
                                    <span style="color: var(--muted); font-size: 12px;">All Inquiries</span>
                                @endif
                            </td>
                            <td style="padding: 14px 12px;">
                                <span style="font-weight: 600; color: #334155;">{{ count($form->schema ?? []) }}</span>
                                <span style="font-size: 12px; color: var(--muted);">fields</span>
                            </td>
                            <td style="padding: 14px 12px;">
                                @if ($form->status === 'published')
                                    <span class="pill" style="background: #ecfdf5; color: #16a34a; font-weight: 700;">● Published (v{{ $form->version }})</span>
                                @elseif ($form->status === 'draft')
                                    <span class="pill" style="background: #fffbeb; color: #b45309; font-weight: 700;">○ Draft (v{{ $form->version }})</span>
                                @else
                                    <span class="pill" style="background: #f1f5f9; color: #64748b;">Archived (v{{ $form->version }})</span>
                                @endif
                            </td>
                            <td style="padding: 14px 12px; font-size: 12px; color: var(--muted);">
                                {{ $form->updated_at->diffForHumans() }}
                                <div style="font-size: 11px;">By {{ $form->updatedBy->name ?? 'Admin' }}</div>
                            </td>
                            <td style="padding: 14px 12px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <a href="{{ route('admin.support.forms.edit', $form->id) }}" class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.support.forms.preview', $form->id) }}" class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">
                                        Preview
                                    </a>
                                    
                                    @if ($form->status === 'draft')
                                        <form method="POST" action="{{ route('admin.support.forms.publish', $form->id) }}" onsubmit="return confirm('Publish form {{ $form->name }} as live v{{ $form->version + 1 }}?')" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: #16a34a; box-shadow: none;">
                                                Publish
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.support.forms.duplicate', $form->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="button" title="Duplicate form" style="width: auto; padding: 6px 10px; font-size: 12px; background: #f8fafc; color: #475569; border: 1px solid var(--line); box-shadow: none;">
                                            📋
                                        </button>
                                    </form>

                                    @if ($form->status !== 'archived')
                                        <form method="POST" action="{{ route('admin.support.forms.archive', $form->id) }}" onsubmit="return confirm('Archive form {{ $form->name }}? Customers will no longer be able to submit it.')" style="display: inline;">
                                            @csrf
                                            <button type="submit" title="Archive form" style="background: transparent; border: 0; color: #94a3b8; cursor: pointer; font-size: 13px; padding: 4px;">
                                                📦
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 36px; text-align: center; color: var(--muted);">
                                <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                                <div style="font-weight: 700; color: var(--ink); font-size: 15px;">No Dynamic Forms Created Yet</div>
                                <div style="font-size: 13px; max-width: 420px; margin: 6px auto 16px;">
                                    Create tailored intake forms with custom text, dropdowns, attachments, and branching rules.
                                </div>
                                <a href="{{ route('admin.support.forms.create') }}" class="button" style="display: inline-block; width: auto; padding: 9px 20px;">
                                    Build Your First Ticket Form
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
