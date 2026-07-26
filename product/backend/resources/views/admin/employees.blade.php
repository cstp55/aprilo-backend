@extends('admin.layout')

@section('title', 'Employee Directory & Validation - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">HR Operations</div>
            <h1>Employee Directory & ID Validation</h1>
            <p class="help">Validate active employee ID cards and view user registry details.</p>
        </div>
    </div>

    <!-- Employee ID Validation Tool -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin-bottom: 12px; font-size: 16px;">Employee ID Card Validation</h3>
        <p class="help" style="margin-top: 0; margin-bottom: 16px;">Scan or type an employee ID below to check its validity and active status.</p>
        
        <form method="POST" action="{{ route('admin.employees.validate') }}" style="margin: 0; padding: 0; max-width: 480px; border: 0; box-shadow: none; display: flex; gap: 8px; align-items: flex-end;">
            @csrf
            <div style="flex: 1;">
                <label class="field" style="font-size: 11px;">
                    Employee ID
                    <input name="employee_id" type="text" placeholder="e.g. EMP-2026-987" value="{{ $searchedEmployee }}" required style="padding: 10px;">
                </label>
            </div>
            <button type="submit" style="margin: 0; width: auto; height: 38px; padding: 0 20px;">Validate ID</button>
        </form>

        @if ($searchedEmployee)
            <div style="margin-top: 20px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; background: #faf9f6;">
                @if ($validatedEmployee)
                    <!-- Valid Employee Details Card -->
                    <div style="background: #e6f7ed; color: #1f7a3f; font-weight: 700; font-size: 14px; padding: 12px 16px; border-bottom: 1px solid #ccefd8; display: flex; align-items: center; gap: 8px;">
                        <span>🟢 VALID EMPLOYEE IDENTITY - ACTIVE</span>
                    </div>
                    <div style="padding: 20px; display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: center;">
                        <!-- Placeholder Employee ID Badge Photo -->
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: #ece8df; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; font-size: 32px;">
                            👤
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                            <div>
                                <span style="color: var(--muted); font-size: 11px; display: block; font-weight: 600; text-transform: uppercase;">Full Name</span>
                                <strong>{{ $validatedEmployee->name }}</strong>
                            </div>
                            <div>
                                <span style="color: var(--muted); font-size: 11px; display: block; font-weight: 600; text-transform: uppercase;">Employee ID</span>
                                <strong>{{ $validatedEmployee->employee_id }}</strong>
                            </div>
                            <div>
                                <span style="color: var(--muted); font-size: 11px; display: block; font-weight: 600; text-transform: uppercase;">Email Address</span>
                                <span>{{ $validatedEmployee->email }}</span>
                            </div>
                            <div>
                                <span style="color: var(--muted); font-size: 11px; display: block; font-weight: 600; text-transform: uppercase;">ID Card Status</span>
                                @if ($validatedEmployee->idCard)
                                    @if ($validatedEmployee->idCard->status === 'active')
                                        <span class="pill" style="background: #e6f7ed; color: #1f7a3f; font-size: 10px; padding: 2px 6px;">{{ $validatedEmployee->idCard->card_number }} (Active)</span>
                                    @else
                                        <span class="pill" style="background: #fdf2f2; color: #b82c2c; font-size: 10px; padding: 2px 6px;">{{ $validatedEmployee->idCard->card_number }} (Suspended)</span>
                                    @endif
                                @else
                                    <span style="color: #b27300; font-weight: 600;">No ID Card Issued</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Invalid ID Card Alert -->
                    <div style="background: #fdf2f2; color: #b82c2c; font-weight: 700; font-size: 14px; padding: 12px 16px; border-bottom: 1px solid #fbdad9;">
                        ❌ INVALID EMPLOYEE ID - RECORD NOT FOUND
                    </div>
                    <div style="padding: 16px 20px; font-size: 13px; color: var(--muted);">
                        No registered users match the ID <strong>"{{ $searchedEmployee }}"</strong> in the organization.
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Employee List Directory -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--line);">
            <h3 style="font-size: 16px; margin: 0;">Employee Directory</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Employee ID</th>
                    <th>Role</th>
                    <th>Teams User ID</th>
                    <th>Escalation Priority</th>
                    <th>Escalation Routing</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td><strong>{{ $employee->name }}</strong></td>
                        <td>{{ $employee->email }}</td>
                        <td><code>{{ $employee->employee_id ?: 'N/A' }}</code></td>
                        <td>
                            <span class="pill" style="text-transform: capitalize;">{{ str_replace('_', ' ', $employee->role) }}</span>
                        </td>
                        <td>
                            @if ($employee->teams_user_id)
                                <code style="font-size: 11px;">{{ substr($employee->teams_user_id, 0, 12) }}...</code>
                            @else
                                <span style="color: var(--muted); font-style: italic; font-size: 12px;">Not Configured</span>
                            @endif
                        </td>
                        <td>
                            @if (in_array($employee->role, ['hr_admin', 'owner']))
                                <span style="font-weight: 600;">Priority {{ $employee->escalation_priority ?? 99 }}</span>
                            @else
                                <span style="color: var(--muted); font-style: italic; font-size: 12px;">N/A (Employee)</span>
                            @endif
                        </td>
                        <td>
                            @if (in_array($employee->role, ['hr_admin', 'owner']))
                                @if ($employee->escalation_routing_active)
                                    <span class="pill" style="background: #e6f7ed; color: #1f7a3f; font-size: 11px;">Active</span>
                                @else
                                    <span class="pill" style="background: #fdf2f2; color: #b82c2c; font-size: 11px;">Paused</span>
                                @endif
                            @else
                                <span style="color: var(--muted); font-style: italic; font-size: 12px;">N/A</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" onclick="toggleEdit('{{ $employee->id }}')" style="width: auto; margin: 0; padding: 4px 10px; font-size: 11px; height: 26px; border: 1px solid var(--line); background: transparent; color: var(--ink); border-radius: 4px;">Edit Teams</button>
                        </td>
                    </tr>
                    <!-- Inline Edit Form Row -->
                    <tr id="edit-row-{{ $employee->id }}" style="display: none; background: #faf8f5;">
                        <td colspan="8" style="padding: 12px; border-bottom: 1px solid var(--line);">
                            <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}" style="margin: 0; padding: 12px; border: 1px solid var(--line); border-radius: 6px; display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; background: #fff;">
                                @csrf
                                @method('PATCH')
                                
                                <div style="flex: 1; min-width: 200px;">
                                    <label class="field" style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">
                                        Teams User ID (Entra ID Object ID)
                                        <input name="teams_user_id" type="text" value="{{ $employee->teams_user_id }}" placeholder="e.g. 5b2e8a1c-..." style="padding: 6px 10px; margin-top: 4px;">
                                    </label>
                                </div>
                                
                                @if (in_array($employee->role, ['hr_admin', 'owner']))
                                    <div style="width: 120px;">
                                        <label class="field" style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">
                                            Escalation Priority
                                            <input name="escalation_priority" type="number" min="1" max="100" value="{{ $employee->escalation_priority ?? 99 }}" style="padding: 6px 10px; margin-top: 4px;">
                                        </label>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; height: 38px; gap: 8px;">
                                        <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="checkbox" name="escalation_routing_active" value="1" @checked($employee->escalation_routing_active) style="margin: 0;">
                                            Routing Active
                                        </label>
                                    </div>
                                @else
                                    <input type="hidden" name="escalation_priority" value="99">
                                @endif
                                
                                <div style="display: flex; gap: 8px; margin-left: auto;">
                                    <button type="submit" style="width: auto; margin: 0; padding: 0 16px; height: 32px; font-size: 12px; background: var(--green); border: 0; color: #fff; border-radius: 4px; font-weight: 700; cursor: pointer;">Save</button>
                                    <button type="button" onclick="toggleEdit('{{ $employee->id }}')" style="width: auto; margin: 0; padding: 0 16px; height: 32px; font-size: 12px; background: transparent; border: 1px solid var(--line); color: var(--ink); border-radius: 4px; cursor: pointer;">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        function toggleEdit(id) {
            const row = document.getElementById('edit-row-' + id);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>

@endsection
