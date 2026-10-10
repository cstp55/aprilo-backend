@extends('admin.layout')

@section('title', 'Preview: ' . $form->name . ' - Aprilo Support Portal')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold" style="color: var(--green); text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px;">
                Customer Portal Simulation
            </div>
            <h1 style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                {{ $form->name }}
                <span class="pill" style="font-size: 11px; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;
                    background: {{ $form->status === 'published' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(245, 158, 11, 0.1)' }};
                    color: {{ $form->status === 'published' ? '#16a34a' : '#d97706' }};">
                    {{ ucfirst($form->status) }} · v{{ $form->version }}
                </span>
            </h1>
            <p class="help" style="color: var(--muted); margin-top: 4px; font-size: 14px;">
                Live preview of how customers experience this intake form. Conditional visibility rules and validation operate in real-time.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.support.forms.edit', $form->id) }}" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">
                ← Back to Builder
            </a>
            <a href="{{ route('admin.support.forms') }}" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">
                All Forms
            </a>
        </div>
    </div>

    <!-- Portal Preview Container with Device Frame Toggle -->
    <div style="margin-top: 24px; display: flex; justify-content: center; margin-bottom: 16px;">
        <div style="background: #e2e8f0; padding: 4px; border-radius: 8px; display: inline-flex; gap: 4px;">
            <button type="button" id="btnDesktopView" onclick="setPreviewDevice('desktop')" style="border: none; background: #fff; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: var(--ink); box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                🖥️ Desktop
            </button>
            <button type="button" id="btnTabletView" onclick="setPreviewDevice('tablet')" style="border: none; background: transparent; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--muted);">
                📱 Tablet
            </button>
            <button type="button" id="btnMobileView" onclick="setPreviewDevice('mobile')" style="border: none; background: transparent; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--muted);">
                📱 Mobile
            </button>
        </div>
    </div>

    <div style="display: flex; justify-content: center;">
        <div id="previewFrame" style="width: 100%; max-width: 820px; transition: max-width 0.3s ease;">
            <!-- Simulated Portal Chrome -->
            <div style="background: #fff; border-radius: 16px; border: 1px solid var(--line); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); overflow: hidden;">
                <!-- Header Branding Banner -->
                <div style="background: linear-gradient(135deg, {{ $portalSettings->primary_color ?: '#22c55e' }} 0%, {{ $portalSettings->secondary_color ?: '#0f172a' }} 100%); padding: 32px 28px; color: #fff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                        <div>
                            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.85;">
                                {{ $organization->name }} Customer Support
                            </div>
                            <h2 style="margin: 6px 0 0; font-size: 24px; font-weight: 700; color: #fff;">
                                {{ $form->name }}
                            </h2>
                            @if($form->description)
                                <p style="margin: 8px 0 0; font-size: 14px; opacity: 0.9; max-width: 600px;">
                                    {{ $form->description }}
                                </p>
                            @endif
                        </div>
                        @if($portalSettings->logo_url)
                            <img src="{{ $portalSettings->logo_url }}" alt="Logo" style="height: 44px; max-width: 140px; object-fit: contain; background: #fff; padding: 6px; border-radius: 8px;">
                        @endif
                    </div>
                </div>

                <!-- Form Body -->
                <div style="padding: 32px 28px;">
                    <form id="previewSimulationForm" onsubmit="event.preventDefault(); simulateSubmission();">
                        <div id="fieldsContainer" style="display: flex; flex-direction: column; gap: 20px;">
                            <!-- Fields will be dynamically rendered here by JavaScript based on $form->schema -->
                        </div>

                        <!-- Action Controls -->
                        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                            <button type="button" onclick="resetSimulation()" style="background: none; border: 1px solid var(--line); padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--muted);">
                                Reset Form
                            </button>
                            <button type="submit" style="background: {{ $portalSettings->primary_color ?: '#22c55e' }}; color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                Submit Support Ticket
                            </button>
                        </div>
                    </form>

                    <!-- Submission Simulation Outcome Box -->
                    <div id="simulationResult" style="display: none; margin-top: 24px; padding: 18px; border-radius: 10px; font-size: 13px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const formSchema = @json($form->schema ?: []);

    function setPreviewDevice(device) {
        const frame = document.getElementById('previewFrame');
        const btnDesktop = document.getElementById('btnDesktopView');
        const btnTablet = document.getElementById('btnTabletView');
        const btnMobile = document.getElementById('btnMobileView');

        [btnDesktop, btnTablet, btnMobile].forEach(btn => {
            btn.style.background = 'transparent';
            btn.style.boxShadow = 'none';
            btn.style.color = 'var(--muted)';
            btn.style.fontWeight = '500';
        });

        if (device === 'mobile') {
            frame.style.maxWidth = '390px';
            btnMobile.style.background = '#fff';
            btnMobile.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            btnMobile.style.color = 'var(--ink)';
            btnMobile.style.fontWeight = '600';
        } else if (device === 'tablet') {
            frame.style.maxWidth = '640px';
            btnTablet.style.background = '#fff';
            btnTablet.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            btnTablet.style.color = 'var(--ink)';
            btnTablet.style.fontWeight = '600';
        } else {
            frame.style.maxWidth = '820px';
            btnDesktop.style.background = '#fff';
            btnDesktop.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            btnDesktop.style.color = 'var(--ink)';
            btnDesktop.style.fontWeight = '600';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderFields() {
        const container = document.getElementById('fieldsContainer');
        container.innerHTML = '';

        formSchema.forEach(field => {
            const fieldWrapper = document.createElement('div');
            fieldWrapper.id = `wrapper_${field.key}`;
            fieldWrapper.className = 'form-field-wrapper';
            fieldWrapper.style.display = 'flex';
            fieldWrapper.style.flexDirection = 'column';
            fieldWrapper.style.gap = '6px';

            const labelEl = document.createElement('label');
            labelEl.style.fontSize = '13px';
            labelEl.style.fontWeight = '600';
            labelEl.style.color = 'var(--ink)';
            labelEl.style.display = 'flex';
            labelEl.style.alignItems = 'center';
            labelEl.style.gap = '4px';

            labelEl.innerHTML = escapeHtml(field.label || field.key) + 
                (field.required ? ' <span style="color: #ef4444;">*</span>' : ' <span style="font-size: 11px; font-weight: normal; color: var(--muted);">(Optional)</span>');

            fieldWrapper.appendChild(labelEl);

            let inputEl;
            const inputStyles = 'width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; font-size: 14px; font-family: inherit; box-sizing: border-box; background: #fff; color: var(--ink);';

            switch (field.type) {
                case 'textarea':
                    inputEl = document.createElement('textarea');
                    inputEl.style.cssText = inputStyles + ' min-height: 100px; resize: vertical;';
                    inputEl.placeholder = field.placeholder || '';
                    break;
                case 'select':
                    inputEl = document.createElement('select');
                    inputEl.style.cssText = inputStyles;
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = field.placeholder || '-- Please Select --';
                    inputEl.appendChild(defaultOpt);
                    (field.options || []).forEach(opt => {
                        const o = document.createElement('option');
                        o.value = opt.value ?? opt;
                        o.textContent = opt.label ?? opt;
                        inputEl.appendChild(o);
                    });
                    break;
                case 'radio':
                    inputEl = document.createElement('div');
                    inputEl.style.display = 'flex';
                    inputEl.style.flexDirection = 'column';
                    inputEl.style.gap = '8px';
                    (field.options || []).forEach(opt => {
                        const val = opt.value ?? opt;
                        const lbl = opt.label ?? opt;
                        const row = document.createElement('label');
                        row.style.display = 'flex';
                        row.style.alignItems = 'center';
                        row.style.gap = '8px';
                        row.style.fontSize = '13px';
                        row.style.cursor = 'pointer';
                        row.innerHTML = `<input type="radio" name="${escapeHtml(field.key)}" value="${escapeHtml(val)}" onchange="evaluateConditions()"> <span>${escapeHtml(lbl)}</span>`;
                        inputEl.appendChild(row);
                    });
                    break;
                case 'checkbox':
                    inputEl = document.createElement('div');
                    inputEl.style.display = 'flex';
                    inputEl.style.flexDirection = 'column';
                    inputEl.style.gap = '8px';
                    (field.options || []).forEach(opt => {
                        const val = opt.value ?? opt;
                        const lbl = opt.label ?? opt;
                        const row = document.createElement('label');
                        row.style.display = 'flex';
                        row.style.alignItems = 'center';
                        row.style.gap = '8px';
                        row.style.fontSize = '13px';
                        row.style.cursor = 'pointer';
                        row.innerHTML = `<input type="checkbox" name="${escapeHtml(field.key)}[]" value="${escapeHtml(val)}" onchange="evaluateConditions()"> <span>${escapeHtml(lbl)}</span>`;
                        inputEl.appendChild(row);
                    });
                    break;
                case 'file':
                    inputEl = document.createElement('input');
                    inputEl.type = 'file';
                    inputEl.style.cssText = inputStyles;
                    break;
                default:
                    inputEl = document.createElement('input');
                    inputEl.type = field.type || 'text';
                    inputEl.style.cssText = inputStyles;
                    inputEl.placeholder = field.placeholder || '';
            }

            if (field.type !== 'radio' && field.type !== 'checkbox') {
                inputEl.id = `input_${field.key}`;
                inputEl.name = field.key;
                inputEl.addEventListener('input', evaluateConditions);
                inputEl.addEventListener('change', evaluateConditions);
                fieldWrapper.appendChild(inputEl);
            } else {
                fieldWrapper.appendChild(inputEl);
            }

            if (field.help_text) {
                const helpEl = document.createElement('div');
                helpEl.style.fontSize = '12px';
                helpEl.style.color = 'var(--muted)';
                helpEl.textContent = field.help_text;
                fieldWrapper.appendChild(helpEl);
            }

            const errEl = document.createElement('div');
            errEl.id = `error_${field.key}`;
            errEl.style.color = '#ef4444';
            errEl.style.fontSize = '12px';
            errEl.style.display = 'none';
            fieldWrapper.appendChild(errEl);

            container.appendChild(fieldWrapper);
        });

        evaluateConditions();
    }

    function getFormValues() {
        const values = {};
        formSchema.forEach(field => {
            if (field.type === 'radio') {
                const checked = document.querySelector(`input[name="${field.key}"]:checked`);
                values[field.key] = checked ? checked.value : '';
            } else if (field.type === 'checkbox') {
                const checkedList = Array.from(document.querySelectorAll(`input[name="${field.key}[]"]:checked`)).map(el => el.value);
                values[field.key] = checkedList;
            } else {
                const el = document.getElementById(`input_${field.key}`);
                values[field.key] = el ? el.value : '';
            }
        });
        return values;
    }

    function evaluateConditions() {
        const values = getFormValues();

        formSchema.forEach(field => {
            const wrapper = document.getElementById(`wrapper_${field.key}`);
            if (!wrapper) return;

            let isVisible = true;
            if (field.condition && field.condition.field) {
                const dependKey = field.condition.field;
                const expectedVal = String(field.condition.value ?? '');
                const currentVal = values[dependKey];

                const operator = field.condition.operator || 'equals';
                if (operator === 'equals') {
                    if (Array.isArray(currentVal)) {
                        isVisible = currentVal.includes(expectedVal);
                    } else {
                        isVisible = String(currentVal ?? '').toLowerCase() === expectedVal.toLowerCase();
                    }
                } else if (operator === 'not_equals') {
                    isVisible = String(currentVal ?? '').toLowerCase() !== expectedVal.toLowerCase();
                } else if (operator === 'filled') {
                    isVisible = currentVal !== '' && currentVal !== null && currentVal !== undefined && (!Array.isArray(currentVal) || currentVal.length > 0);
                }
            }

            wrapper.style.display = isVisible ? 'flex' : 'none';
        });
    }

    function simulateSubmission() {
        const values = getFormValues();
        let isValid = true;
        const submittedData = {};

        // Clear previous error messages
        formSchema.forEach(field => {
            const errEl = document.getElementById(`error_${field.key}`);
            if (errEl) {
                errEl.style.display = 'none';
                errEl.textContent = '';
            }
        });

        formSchema.forEach(field => {
            const wrapper = document.getElementById(`wrapper_${field.key}`);
            if (!wrapper || wrapper.style.display === 'none') {
                return; // conditionally hidden fields are ignored
            }

            const val = values[field.key];
            if (field.required && (val === '' || val === null || val === undefined || (Array.isArray(val) && val.length === 0))) {
                isValid = false;
                const errEl = document.getElementById(`error_${field.key}`);
                if (errEl) {
                    errEl.textContent = `${field.label || field.key} is required.`;
                    errEl.style.display = 'block';
                }
            } else {
                submittedData[field.key] = val;
            }
        });

        const resEl = document.getElementById('simulationResult');
        resEl.style.display = 'block';

        if (!isValid) {
            resEl.style.background = '#fef2f2';
            resEl.style.border = '1px solid #fecaca';
            resEl.style.color = '#991b1b';
            resEl.innerHTML = `<strong>Validation Failed:</strong> Please correct the highlighted errors before submitting.`;
        } else {
            resEl.style.background = '#f0fdf4';
            resEl.style.border = '1px solid #bbf7d0';
            resEl.style.color = '#166534';
            resEl.innerHTML = `
                <div style="font-weight: 700; margin-bottom: 6px;">
                    ✅ Ticket Submission Verified!
                </div>
                <div>Form logic and field constraints validated successfully. Collected payload:</div>
                <pre style="background: rgba(0,0,0,0.05); padding: 10px; border-radius: 6px; margin-top: 8px; overflow-x: auto; font-family: monospace; font-size: 12px;">${JSON.stringify(submittedData, null, 2)}</pre>
            `;
        }
    }

    function resetSimulation() {
        document.getElementById('previewSimulationForm').reset();
        evaluateConditions();
        const resEl = document.getElementById('simulationResult');
        resEl.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', renderFields);
</script>
@endpush
