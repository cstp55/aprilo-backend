@extends('admin.layout')

@section('title', 'Aprilo AI Admin Dashboard')

@section('content')
    <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="eyebrow">Operations</div>
            <h1>Admin Dashboard</h1>
            <p class="help">Manage the operational layer behind Aprilo AI: approved knowledge, escalations, and ROI signals.</p>
        </div>
        
        <!-- Local environment shortcut info -->
        <div style="background: rgba(210,38,48,0.06); border: 1px solid rgba(210,38,48,0.15); border-radius: 8px; padding: 10px 16px; font-size: 12px; max-width: 320px;">
            <strong>Console Login / Local Access URL</strong>
            <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                <code style="font-family: monospace; color: var(--green); background: rgba(0,0,0,0.05); padding: 2px 4px; border-radius: 4px;">localhost.aprilo.com/admin/login</code>
                <a href="{{ route('login') }}" target="_blank" style="color: var(--green); text-decoration: underline; font-weight: 700;">Login Page</a>
            </div>
        </div>
    </div>

    <!-- Metric grid -->
    <section class="grid grid-4">
        <div class="card">
            <div class="metric">{{ $summary['total_questions'] }}</div>
            <div class="label">Total questions</div>
        </div>
        <div class="card">
            <div class="metric">{{ $summary['resolved_questions'] }}</div>
            <div class="label">Resolved questions</div>
        </div>
        <div class="card">
            <div class="metric">{{ $openEscalationCount }}</div>
            <div class="label">Open escalations</div>
        </div>
        <div class="card">
            <div class="metric">{{ $summary['estimated_hours_saved'] }}</div>
            <div class="label">Estimated hours saved</div>
        </div>
    </section>

    <!-- Billing & Usage Metrics -->
    <section class="grid grid-3" style="margin-top: 16px; margin-bottom: 16px;">
        <div class="card" style="border-left: 4px solid var(--green);">
            <div class="eyebrow" style="text-transform: uppercase; font-size: 10px; font-weight: 700; color: var(--muted);">Billing Mode</div>
            <h3 style="font-size: 18px; margin: 8px 0 4px; text-transform: capitalize;">{{ str_replace('_', ' ', $settings->billing_mode) }}</h3>
            <p style="font-size: 12px; color: var(--muted); margin: 0;">Plan Tier: <strong style="text-transform: capitalize;">{{ $organization->plan }}</strong></p>
        </div>
        <div class="card" style="border-left: 4px solid var(--green);">
            <div class="eyebrow" style="text-transform: uppercase; font-size: 10px; font-weight: 700; color: var(--muted);">Monthly Accrued Charges</div>
            <h3 style="font-size: 18px; margin: 8px 0 4px; color: var(--green);">
                @if ($settings->billing_mode === 'pay_as_you_go')
                    ${{ number_format($settings->usage_amount_due, 2) }}
                @else
                    ${{ number_format($organization->plan === 'pro' ? 49.00 : 199.00, 2) }}
                @endif
            </h3>
            <p style="font-size: 12px; color: var(--muted); margin: 0;">Auto-debit status: <strong>Active 🟢</strong></p>
        </div>
        <div class="card" style="border-left: 4px solid var(--green);">
            <div class="eyebrow" style="text-transform: uppercase; font-size: 10px; font-weight: 700; color: var(--muted);">Queries Processed</div>
            <h3 style="font-size: 18px; margin: 8px 0 4px;">{{ $settings->usage_queries_count }} queries</h3>
            <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" style="font-size: 12px; color: var(--green); text-decoration: underline; font-weight: 700;">Manage Billing Settings</a>
        </div>
    </section>

    <!-- Side-by-Side Info Cards -->
    <section class="grid grid-2" style="margin-top:16px;">
        <div class="card">
            <div class="eyebrow">Knowledge health</div>
            <h2>{{ $indexedSourceCount }} of {{ $sourceCount }} sources indexed</h2>
            <p class="help">Indexed sources are available to the customer-facing question experience.</p>
        </div>
        <div class="card">
            <div class="eyebrow">Customer app</div>
            <h2>Next.js customer UI</h2>
            <p class="help">Employees and customers use the separate Aprilo AI frontend for asking questions and viewing citations.</p>
        </div>
    </section>

    <!-- Interactive Working Design Workflow Diagram (Leaves Query & RAG Pipeline) -->
    <section class="card" style="margin-top: 24px;">
        <div class="eyebrow">RAG & Reasoning Architecture</div>
        <h2 style="margin-bottom: 16px;">Interactive Workflow Diagram (Drag & Drop to Reorder)</h2>
        <p class="help" style="margin-top: 0; margin-bottom: 24px;">
            Below is the flow showing how employee leaves inquiries are intercepted, validated, and resolved. Drag and drop any card to reorder nodes dynamically:
        </p>

        <!-- Drag & Drop Container -->
        <div id="drag-drop-workflow-container" style="display: flex; gap: 16px; align-items: stretch; justify-content: space-between; background: #faf9f6; padding: 20px; border-radius: 12px; border: 1px solid var(--line); flex-wrap: wrap;">
            <!-- Step 1 -->
            <div class="workflow-card" draggable="true" style="flex: 1; min-width: 180px; background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); cursor: grab; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--green); margin-bottom: 8px; text-transform: uppercase;">1. User Input</div>
                    <strong style="font-size: 13px; display: block; margin-bottom: 6px;">Leaves Query</strong>
                    <div style="font-size: 11px; color: var(--muted); line-height: 1.4;">"How many remaining leaves do I have?"</div>
                </div>
                <div style="margin-top: 12px; font-size: 12px; color: var(--green); font-weight: bold; align-self: flex-end;">⋮ Drag</div>
            </div>

            <!-- Step 2 -->
            <div class="workflow-card" draggable="true" style="flex: 1; min-width: 180px; background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); cursor: grab; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--green); margin-bottom: 8px; text-transform: uppercase;">2. Intent Classification</div>
                    <strong style="font-size: 13px; display: block; margin-bottom: 6px;">ID Validation Triggered</strong>
                    <div style="font-size: 11px; color: var(--muted); line-height: 1.4;">Prompts employee: "Please enter your employee ID to verify."</div>
                </div>
                <div style="margin-top: 12px; font-size: 12px; color: var(--green); font-weight: bold; align-self: flex-end;">⋮ Drag</div>
            </div>

            <!-- Step 3 -->
            <div class="workflow-card" draggable="true" style="flex: 1; min-width: 180px; background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); cursor: grab; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--green); margin-bottom: 8px; text-transform: uppercase;">3. Laravel API Request</div>
                    <strong style="font-size: 13px; display: block; margin-bottom: 6px;">Fetch Balances</strong>
                    <div style="font-size: 11px; color: var(--muted); line-height: 1.4;">Queries leave balances dynamically for validated ID.</div>
                </div>
                <div style="margin-top: 12px; font-size: 12px; color: var(--green); font-weight: bold; align-self: flex-end;">⋮ Drag</div>
            </div>

            <!-- Step 4 -->
            <div class="workflow-card" draggable="true" style="flex: 1; min-width: 180px; background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); cursor: grab; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--green); margin-bottom: 8px; text-transform: uppercase;">4. Reasoning Engine</div>
                    <strong style="font-size: 13px; display: block; margin-bottom: 6px;">LLM Formatter</strong>
                    <div style="font-size: 11px; color: var(--muted); line-height: 1.4;">Generates conversational answer and references knowledge citations.</div>
                </div>
                <div style="margin-top: 12px; font-size: 12px; color: var(--green); font-weight: bold; align-self: flex-end;">⋮ Drag</div>
            </div>

            <!-- Step 5 -->
            <div class="workflow-card" draggable="true" style="flex: 1; min-width: 180px; background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); cursor: grab; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--green); margin-bottom: 8px; text-transform: uppercase;">5. Dispatch Alert</div>
                    <strong style="font-size: 13px; display: block; margin-bottom: 6px;">Connected Medium</strong>
                    <div style="font-size: 11px; color: var(--muted); line-height: 1.4;">Pushes notification log to Microsoft Teams / Skype webhook.</div>
                </div>
                <div style="margin-top: 12px; font-size: 12px; color: var(--green); font-weight: bold; align-self: flex-end;">⋮ Drag</div>
            </div>
        </div>
    </section>

    <!-- Invoice History Section -->
    <section class="card" style="margin-top: 24px;">
        <div class="eyebrow" style="text-transform: uppercase; font-size: 10px; font-weight: 700; color: var(--muted);">Billing & Invoices</div>
        <h2 style="margin-bottom: 12px; font-size: 16px;">Invoice & Payment History</h2>
        
        @if ($invoices->isEmpty())
            <div style="text-align: center; padding: 30px; color: var(--muted); font-size: 13px; background: #faf8f5; border: 1px solid var(--line); border-radius: 8px;">
                No invoices generated yet. Accrue query traffic and generate invoices under <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" style="color: var(--green); text-decoration: underline; font-weight: 700;">Billing Settings</a>.
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; margin-top: 10px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--line); color: var(--muted); background: #fafafa;">
                        <th style="padding: 10px 12px;">Invoice Number</th>
                        <th style="padding: 10px 12px;">Billing Period</th>
                        <th style="padding: 10px 12px;">Billing Mode</th>
                        <th style="padding: 10px 12px;">Amount</th>
                        <th style="padding: 10px 12px;">Status</th>
                        <th style="padding: 10px 12px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr style="border-bottom: 1px solid var(--line);">
                            <td style="padding: 12px 12px; font-weight: 700;">{{ $invoice->invoice_number }}</td>
                            <td style="padding: 12px 12px; color: var(--muted);">
                                {{ $invoice->billing_period_start->format('M d') }} - {{ $invoice->billing_period_end->format('M d, Y') }}
                            </td>
                            <td style="padding: 12px 12px; text-transform: capitalize;">{{ str_replace('_', ' ', $invoice->billing_mode) }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--green);">${{ number_format($invoice->amount, 2) }}</td>
                            <td style="padding: 12px 12px;">
                                <span style="background: #e6f7ed; color: #1f7a3f; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px;">{{ ucfirst($invoice->status) }}</span>
                            </td>
                            <td style="padding: 12px 12px; text-align: right;">
                                <a href="{{ route('admin.settings.invoices.show', $invoice->id) }}" class="button" style="font-size: 11px; padding: 6px 12px; width: auto; display: inline-block; text-decoration: none;">View Invoice PDF</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <!-- Drag & Drop JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('drag-drop-workflow-container');
            let dragSrcEl = null;

            function handleDragStart(e) {
                this.style.opacity = '0.4';
                this.style.border = '1px dashed var(--green)';
                dragSrcEl = this;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);
            }

            function handleDragOver(e) {
                if (e.preventDefault) {
                    e.preventDefault();
                }
                e.dataTransfer.dropEffect = 'move';
                return false;
            }

            function handleDragEnter(e) {
                this.style.background = '#fef1f2';
            }

            function handleDragLeave(e) {
                this.style.background = '#ffffff';
            }

            function handleDrop(e) {
                if (e.stopPropagation) {
                    e.stopPropagation();
                }
                
                if (dragSrcEl !== this) {
                    dragSrcEl.innerHTML = this.innerHTML;
                    this.innerHTML = e.dataTransfer.getData('text/html');
                }
                return false;
            }

            function handleDragEnd(e) {
                this.style.opacity = '1';
                this.style.border = '1px solid var(--line)';
                
                const cards = container.querySelectorAll('.workflow-card');
                cards.forEach(function (card) {
                    card.style.background = '#ffffff';
                    card.style.border = '1px solid var(--line)';
                });
            }

            const cards = container.querySelectorAll('.workflow-card');
            cards.forEach(function(card) {
                card.addEventListener('dragstart', handleDragStart, false);
                card.addEventListener('dragenter', handleDragEnter, false);
                card.addEventListener('dragover', handleDragOver, false);
                card.addEventListener('dragleave', handleDragLeave, false);
                card.addEventListener('drop', handleDrop, false);
                card.addEventListener('dragend', handleDragEnd, false);
            });
        });
    </script>
@endsection
