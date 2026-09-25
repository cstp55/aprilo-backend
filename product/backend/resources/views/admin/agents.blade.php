@extends('admin.layout')

@section('title', 'Live Chat Agents - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">Live Chat Support</div>
            <h1>Support Agents</h1>
            <p class="help">Create and manage the people who reply to escalated chatbot conversations for {{ $organization->name }}.</p>
        </div>
        <div class="pill" style="background: {{ $featureEnabled ? '#ecfdf5' : '#fffbeb' }}; color: {{ $featureEnabled ? '#047857' : '#b45309' }};">
            {{ $featureEnabled ? 'Live chat included' : 'Subscription required' }}
        </div>
    </div>

    @if (! $featureEnabled)
        <div class="card" style="border-color: #fcd34d; background: #fffbeb; color: #92400e;">
            <h3 style="font-size: 17px; margin-bottom: 8px;">Live chat agents are not available</h3>
            <p style="margin: 0; font-size: 14px; line-height: 1.6;">Your organization needs an active or trialing AI Support subscription before agents can log in and reply to chatbot queries.</p>
        </div>
    @else
        <div class="grid grid-2" style="align-items: start; margin-bottom: 24px;">
            <div class="card">
                <h2 style="font-size: 20px; margin-bottom: 6px;">Create support agent</h2>
                <p class="help" style="margin: 0 0 18px;">The username and password are used by the agent to sign in to the live chat console.</p>
                <form method="POST" action="{{ route('admin.agents.store', $isSuperAdmin ? ['organization_id' => $organization->id] : []) }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                    @csrf
                    <div class="grid grid-2">
                        <label class="field">Full name<input name="full_name" value="{{ old('full_name') }}" required></label>
                        <label class="field">Chat nickname<input name="nickname" value="{{ old('nickname') }}" placeholder="Shown to visitors" required></label>
                        <label class="field">Username<input name="username" value="{{ old('username') }}" autocomplete="username" required></label>
                        <label class="field">Email <span style="font-weight: 400; color: var(--muted);">optional</span><input name="email" type="email" value="{{ old('email') }}"></label>
                        <label class="field">Password<input name="password" type="password" minlength="8" autocomplete="new-password" required></label>
                        <label class="field">Initial status<select name="availability_status" required><option value="offline">Offline</option><option value="away">Away</option><option value="online">Online</option></select></label>
                    </div>
                    <label class="field" style="margin-top: 16px;">Availability slots <span style="font-weight: 400; color: var(--muted);">comma separated</span><input name="availability_slots" value="{{ old('availability_slots') }}" placeholder="09:00-13:00, 14:00-18:00"></label>
                    <button class="button" type="submit" style="width: auto; margin-top: 18px;">Create agent</button>
                </form>
            </div>

            <div class="card">
                <h2 style="font-size: 20px; margin-bottom: 12px;">Organization access</h2>
                <div style="display: grid; gap: 12px; font-size: 14px;">
                    <div><span class="label" style="display: block; margin: 0;">Organization</span><strong>{{ $organization->name }}</strong></div>
                    <div><span class="label" style="display: block; margin: 0;">Agent seats used</span><strong>{{ $agents->count() }}{{ $agentLimit ? ' / ' . $agentLimit : '' }}</strong></div>
                    <div><span class="label" style="display: block; margin: 0;">Live chat setting</span><strong>{{ ($settings->live_chat_enabled ?? true) ? 'Enabled' : 'Disabled' }}</strong></div>
                    <div><span class="label" style="display: block; margin: 0;">Agent login</span><strong>Username and password</strong></div>
                </div>
            </div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 18px 20px; border-bottom: 1px solid var(--line);">
                <h2 style="font-size: 20px;">Organization agents</h2>
            </div>
            @forelse ($agents as $agent)
                <div style="padding: 20px; border-bottom: 1px solid var(--line);">
                    <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
                        <div>
                            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <h3 style="font-size: 17px;">{{ $agent->nickname }}</h3>
                                <span class="pill" style="background: {{ $agent->is_active ? '#ecfdf5' : '#fef2f2' }}; color: {{ $agent->is_active ? '#047857' : '#b91c1c' }};">{{ $agent->is_active ? ucfirst($agent->availability_status) : 'Deactivated' }}</span>
                            </div>
                            <p style="margin: 5px 0 0; color: var(--muted); font-size: 13px;">{{ $agent->user->name }} · <code>{{ '@' . $agent->user->username }}</code>{{ $agent->user->email ? ' · ' . $agent->user->email : '' }}</p>
                            @if ($agent->availability_slots)
                                <p style="margin: 8px 0 0; color: var(--muted); font-size: 12px;">Slots: {{ implode(', ', $agent->availability_slots) }}</p>
                            @endif
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="button" type="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" onclick="toggleAgentEdit('{{ $agent->id }}')">Edit</button>
                            <form method="POST" action="{{ route('admin.agents.destroy', array_merge(['agentSupport' => $agent], $isSuperAdmin ? ['organization_id' => $organization->id] : [])) }}" onsubmit="return confirm('Delete this agent and their login?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="width: auto; background: #fff; color: var(--danger); border: 1px solid #fecaca; border-radius: 8px; padding: 11px 16px; font-weight: 700; cursor: pointer;">Delete</button>
                            </form>
                        </div>
                    </div>

                    <form id="agent-edit-{{ $agent->id }}" method="POST" action="{{ route('admin.agents.update', array_merge(['agentSupport' => $agent], $isSuperAdmin ? ['organization_id' => $organization->id] : [])) }}" style="display: none; margin: 18px 0 0; padding: 18px; border: 1px solid var(--line); border-radius: 8px; box-shadow: none;">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-2">
                            <label class="field">Full name<input name="full_name" value="{{ $agent->user->name }}" required></label>
                            <label class="field">Chat nickname<input name="nickname" value="{{ $agent->nickname }}" required></label>
                            <label class="field">Username<input name="username" value="{{ $agent->user->username }}" required></label>
                            <label class="field">Email<input name="email" type="email" value="{{ $agent->user->email }}"></label>
                            <label class="field">New password <span style="font-weight: 400; color: var(--muted);">optional</span><input name="password" type="password" minlength="8" autocomplete="new-password"></label>
                            <label class="field">Availability<select name="availability_status" required><option value="offline" @selected($agent->availability_status === 'offline')>Offline</option><option value="away" @selected($agent->availability_status === 'away')>Away</option><option value="online" @selected($agent->availability_status === 'online')>Online</option></select></label>
                        </div>
                        <label class="field" style="margin-top: 16px;">Availability slots<input name="availability_slots" value="{{ implode(', ', $agent->availability_slots ?? []) }}"></label>
                        <label style="display: flex; gap: 8px; align-items: center; margin-top: 16px; font-size: 13px; font-weight: 700;"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($agent->is_active)> Active and allowed to log in</label>
                        <div style="display: flex; gap: 8px; margin-top: 18px;"><button class="button" type="submit" style="width: auto;">Save changes</button><button type="button" onclick="toggleAgentEdit('{{ $agent->id }}')" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line); border-radius: 8px; padding: 11px 16px; font-weight: 700; cursor: pointer;">Cancel</button></div>
                    </form>
                </div>
            @empty
                <div style="padding: 24px; color: var(--muted); font-size: 14px;">No support agents have been created for this organization.</div>
            @endforelse
        </div>
    @endif

    <script>
        function toggleAgentEdit(id) {
            const form = document.getElementById('agent-edit-' + id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
@endsection
