@extends('admin.layout')

@section('title', 'ID Card Management - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">HR Operations</div>
            <h1>ID Card Management</h1>
            <p class="help">Issue new physical/digital ID cards and update validity statuses.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 24px; align-items: start;">
        <!-- ID Cards List -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--line);">
                <h3 style="font-size: 15px; margin: 0;">Issued ID Cards</h3>
            </div>
            @if ($idcards->isEmpty())
                <div style="padding: 30px; text-align: center; color: var(--muted);">
                    No ID cards have been issued.
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Card Number</th>
                            <th>Employee</th>
                            <th>Issue Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($idcards as $card)
                            <tr>
                                <td><code><strong>{{ $card->card_number }}</strong></code></td>
                                <td>
                                    <strong>{{ $card->user->name }}</strong>
                                    <div style="font-size: 11px; color: var(--muted);">ID: {{ $card->user->employee_id ?: 'N/A' }}</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($card->issue_date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($card->expiry_date)->format('M d, Y') }}</td>
                                <td>
                                    @if ($card->status === 'active')
                                        <span class="pill" style="background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8;">Active</span>
                                    @elseif ($card->status === 'suspended')
                                        <span class="pill" style="background: #fdf2f2; color: #b82c2c; border: 1px solid #fbdad9;">Suspended</span>
                                    @else
                                        <span class="pill" style="background: #fbf0f0; color: #7f8c8d; border: 1px solid #e2e8f0;">Expired</span>
                                    @endif
                                </td>
                                <td style="text-align: right; vertical-align: middle;">
                                    <form method="POST" action="{{ route('admin.idcards.update', $card->id) }}" style="margin: 0; padding: 0; width: auto; border: 0; box-shadow: none;">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" style="font-size: 12px; padding: 4px; border-radius: 4px; border: 1px solid #cfc7b8;">
                                            <option value="active" @selected($card->status === 'active')>Activate</option>
                                            <option value="suspended" @selected($card->status === 'suspended')>Suspend</option>
                                            <option value="expired" @selected($card->status === 'expired')>Expire</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Issue New Card Panel -->
        <div class="card">
            <h3 style="margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Issue ID Card</h3>
            
            @if ($usersWithoutCard->isEmpty())
                <p style="font-size: 13px; color: var(--muted);">All active employees currently have an issued ID card.</p>
            @else
                <form method="POST" action="{{ route('admin.idcards.store') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                    @csrf
                    <div style="display: grid; gap: 14px;">
                        <label class="field">
                            Select Employee
                            <select name="user_id" required>
                                <option value="" disabled selected>-- Select Employee --</option>
                                @foreach ($usersWithoutCard as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->employee_id ?: 'N/A' }})</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field">
                            Card Number
                            <input name="card_number" type="text" placeholder="e.g. APR-54321-EMP" required>
                        </label>

                        <label class="field">
                            Issue Date
                            <input name="issue_date" type="date" value="{{ date('Y-m-d') }}" required>
                        </label>

                        <label class="field">
                            Expiry Date
                            <input name="expiry_date" type="date" value="{{ date('Y-m-d', strtotime('+3 years')) }}" required>
                        </label>

                        <button type="submit" style="margin-top: 8px;">Issue Card</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
