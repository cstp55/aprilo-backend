@extends('admin.layout')

@section('title', 'Aprilo Support - Dynamic Form Builder')

@section('content')
    <form id="formBuilderForm" method="POST" action="{{ $isNew ? route('admin.support.forms.store') : route('admin.support.forms.update', $form->id) }}">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <input type="hidden" name="schema" id="schemaJsonInput" value="{{ json_encode($form->schema ?? []) }}">
        <input type="hidden" name="settings" id="settingsJsonInput" value="{{ json_encode($form->settings ?? []) }}">
        <input type="hidden" name="status" id="formStatusInput" value="{{ $form->status ?? 'draft' }}">

        <!-- Top Navigation & Action Header -->
        <div class="header" style="position: sticky; top: 72px; z-index: 10; background: var(--paper); padding: 12px 0; border-bottom: 1px solid var(--line); margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('admin.support.forms') }}" style="color: var(--muted); font-size: 13px; font-weight: 600;">← Back to Forms</a>
                    <span style="color: var(--line);">/</span>
                    <span class="eyebrow" style="color: var(--green); font-size: 11px;">
                        {{ $isNew ? 'New Form' : 'Editing Form (v' . $form->version . ')' }}
                    </span>
                </div>
                <h1 style="font-size: 24px; margin-top: 4px; display: flex; align-items: center; gap: 12px;">
                    <input type="text" name="name" id="formNameInput" value="{{ old('name', $form->name) }}" required placeholder="Form Title (e.g. IT Helpdesk Request)" style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; border: 1px solid transparent; background: transparent; padding: 4px 8px; border-radius: 6px; width: 450px; outline: none; transition: border 0.2s;" onfocus="this.style.background='#fff'; this.style.borderColor='var(--green)'" onblur="this.style.background='transparent'; this.style.borderColor='transparent'">
                </h1>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" onclick="openLivePreviewModal()" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                    <span>👁️</span> Live Preview
                </button>
                <button type="button" onclick="submitForm('draft')" class="button" style="background: #f8fafc; color: #475569; border: 1px solid var(--line); box-shadow: none; font-size: 13px;">
                    Save as Draft
                </button>
                <button type="button" onclick="confirmPublishForm()" class="button" style="background: #16a34a; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🚀</span> Publish Form
                </button>
            </div>
        </div>

        <!-- Meta Configuration Banner -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 18px 24px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; align-items: center;">
                <div>
                    <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); letter-spacing: 0.08em;">Form Description & Instructions</label>
                    <input type="text" name="description" value="{{ old('description', $form->description) }}" placeholder="Helpful guidance displayed to customers above the form fields..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px; font-size: 13px;">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); letter-spacing: 0.08em;">Associate Ticket Category</label>
                    <select name="category_id" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px; font-size: 13px;">
                        <option value="">-- General / Any Category --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $form->category_id === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->department ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: flex; align-items: center; gap: 8px; margin-top: 18px; cursor: pointer;">
                        <input type="checkbox" name="is_default" value="1" {{ $form->is_default ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--green);">
                        <span style="font-size: 13px; font-weight: 600; color: var(--ink);">Set as Organization Default</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- 3-Column Interactive Form Builder Layout -->
        <div style="display: grid; grid-template-columns: 260px 1fr 340px; gap: 24px; align-items: start;">
            
            <!-- Column 1: Field Types Palette -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px; position: sticky; top: 160px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--green); letter-spacing: 0.08em; margin-bottom: 6px;">Field Type Palette</div>
                <h4 style="margin: 0 0 14px; font-size: 14px; font-weight: 700; color: var(--ink);">Add Customer Questions</h4>
                <p style="font-size: 12px; color: var(--muted); margin: 0 0 16px; line-height: 1.4;">
                    Click a question type to add it to your intake form canvas.
                </p>

                <div style="display: grid; gap: 8px;">
                    <button type="button" onclick="addField('text')" class="palette-btn">
                        <span style="font-size: 16px;">🔤</span>
                        <span>Short Text</span>
                    </button>
                    <button type="button" onclick="addField('textarea')" class="palette-btn">
                        <span style="font-size: 16px;">📝</span>
                        <span>Long Text (Multiline)</span>
                    </button>
                    <button type="button" onclick="addField('email')" class="palette-btn">
                        <span style="font-size: 16px;">✉️</span>
                        <span>Email Address</span>
                    </button>
                    <button type="button" onclick="addField('number')" class="palette-btn">
                        <span style="font-size: 16px;">🔢</span>
                        <span>Numeric Field</span>
                    </button>
                    <button type="button" onclick="addField('date')" class="palette-btn">
                        <span style="font-size: 16px;">📅</span>
                        <span>Date Picker</span>
                    </button>
                    <button type="button" onclick="addField('datetime')" class="palette-btn">
                        <span style="font-size: 16px;">⏰</span>
                        <span>Date & Time</span>
                    </button>
                    <button type="button" onclick="addField('select')" class="palette-btn">
                        <span style="font-size: 16px;">🔽</span>
                        <span>Dropdown (Single Select)</span>
                    </button>
                    <button type="button" onclick="addField('radio')" class="palette-btn">
                        <span style="font-size: 16px;">🔘</span>
                        <span>Radio Options</span>
                    </button>
                    <button type="button" onclick="addField('checkbox')" class="palette-btn">
                        <span style="font-size: 16px;">☑️</span>
                        <span>Checkbox & Multi-Select</span>
                    </button>
                    <button type="button" onclick="addField('file')" class="palette-btn">
                        <span style="font-size: 16px;">📎</span>
                        <span>File / Attachment Upload</span>
                    </button>
                </div>
            </div>

            <!-- Column 2: Form Canvas (Ordered Field List) -->
            <div style="min-width: 0;">
                <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px; min-height: 480px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0;">Form Canvas</h3>
                            <span id="fieldCountBadge" style="font-size: 12px; color: var(--muted);">0 fields configured</span>
                        </div>
                        <div style="font-size: 12px; color: var(--muted);">
                            Use ↑ ↓ buttons to reorder questions
                        </div>
                    </div>

                    <!-- Rendered Fields Container -->
                    <div id="fieldsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                        <!-- Dynamic field cards inserted by JS -->
                    </div>

                    <!-- Empty State Prompt -->
                    <div id="canvasEmptyState" style="display: none; padding: 48px 24px; text-align: center; color: var(--muted); border: 2px dashed var(--line); border-radius: 10px; margin-top: 16px;">
                        <div style="font-size: 36px; margin-bottom: 12px;">📑</div>
                        <div style="font-weight: 700; color: var(--ink); font-size: 16px;">No Fields Added Yet</div>
                        <p style="font-size: 13px; max-width: 380px; margin: 8px auto 16px;">
                            Click any field type from the left palette (e.g. Short Text, Email, Dropdown) to start designing your form.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Column 3: Field Inspector & Property Configuration Panel -->
            <div class="card" id="inspectorCard" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px; position: sticky; top: 160px;">
                <div id="inspectorContent">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--muted); letter-spacing: 0.08em; margin-bottom: 6px;">Field Inspector</div>
                    <div style="padding: 24px 12px; text-align: center; color: var(--muted); font-size: 13px;">
                        Select or add any field on the canvas to configure its label, validation, and branching rules.
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal: Live Interactive Form Preview -->
    <div id="livePreviewModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 60; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #fff; border-radius: 14px; max-width: 680px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--ink);">Customer Portal Live Preview</h3>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">Interactive simulation: tests required validations and conditional branching logic</div>
                </div>
                <button type="button" onclick="closeLivePreviewModal()" style="border: 0; background: transparent; font-size: 20px; cursor: pointer;">✕</button>
            </div>
            
            <div style="padding: 24px; overflow-y: auto; flex: 1;" id="previewContainer">
                <!-- Live preview form rendered by JS -->
            </div>

            <div style="padding: 14px 24px; border-top: 1px solid var(--line); background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 14px 14px;">
                <span style="font-size: 12px; color: var(--muted);">Conditional rules evaluate in real-time</span>
                <button type="button" onclick="closeLivePreviewModal()" class="button" style="width: auto; padding: 7px 18px; font-size: 13px;">Close Preview</button>
            </div>
        </div>
    </div>

    <!-- Modal: Confirm Publish Version -->
    <div id="publishConfirmModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 70; align-items: center; justify-content: center;">
        <div style="background: #fff; border-radius: 12px; max-width: 460px; width: 90%; padding: 24px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <span style="font-size: 28px;">🚀</span>
                <div>
                    <h3 style="margin: 0 0 6px; font-size: 17px; font-weight: 700;">Publish Live Version</h3>
                    <p style="font-size: 13px; color: var(--muted); margin: 0 0 16px;">
                        Publishing makes this form immediately live for customer ticket submissions on your portal. A snapshot will be stored in your version history.
                    </p>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 4px;">Change Summary (Optional)</label>
                    <input type="text" id="publishChangeSummary" placeholder="e.g. Added server ID field with conditional logic" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px; margin-bottom: 20px;">
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closePublishModal()" class="button" style="background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">Cancel</button>
                        <button type="button" onclick="executePublish()" class="button" style="background: #16a34a;">Confirm & Publish</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .palette-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transition: all 0.15s ease;
        }
        .palette-btn:hover {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
            transform: translateX(3px);
        }
        .field-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px 16px;
            background: #fff;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .field-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .field-card.selected {
            border-color: var(--green);
            background: #f0fdf4;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        }
    </style>

    <!-- Embedded Form Builder Logic (Deterministic Vanilla JS) -->
    <script>
        // State
        let formFields = [];
        let selectedFieldId = null;

        // Initialize state from existing schema
        try {
            const raw = document.getElementById('schemaJsonInput').value;
            formFields = JSON.parse(raw || '[]');
            if (!Array.isArray(formFields)) formFields = [];
        } catch (e) {
            formFields = [];
        }

        // Render Canvas on boot
        document.addEventListener('DOMContentLoaded', () => {
            renderCanvas();
            if (formFields.length > 0) {
                selectField(formFields[0].id);
            }
        });

        // Generate stable, unique field key
        function generateStableKey(label, type) {
            let base = label ? label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '') : type;
            if (!base) base = 'field';
            let key = base;
            let counter = 1;
            while (formFields.some(f => f.key === key)) {
                key = `${base}_${counter++}`;
            }
            return key;
        }

        // Add Field
        function addField(type) {
            const typeLabels = {
                text: 'Short Text Question',
                textarea: 'Detailed Explanation',
                email: 'Contact Email',
                number: 'Quantity / Count',
                date: 'Target Date',
                datetime: 'Date and Time',
                select: 'Choose Option',
                radio: 'Select One',
                checkbox: 'Multiple Checkboxes',
                file: 'Attachment / Screenshot',
            };

            const label = typeLabels[type] || 'New Question';
            const stableKey = generateStableKey(label, type);

            const newField = {
                id: 'f_' + Math.random().toString(36).substr(2, 9),
                key: stableKey,
                type: type,
                label: label,
                placeholder: '',
                help_text: '',
                required: false,
                default_value: '',
                options: type === 'select' || type === 'radio' || type === 'checkbox' ? [
                    { value: 'option_1', label: 'Option 1' },
                    { value: 'option_2', label: 'Option 2' },
                    { value: 'option_3', label: 'Option 3' },
                ] : [],
                condition: null, // [field: '...', operator: 'equals', value: '...']
                validation: {
                    min_length: null,
                    max_length: null,
                    allowed_file_types: type === 'file' ? '.png,.jpg,.pdf,.docx,.zip' : null,
                    max_file_size_mb: type === 'file' ? 10 : null,
                },
                order: formFields.length + 1,
            };

            formFields.push(newField);
            renderCanvas();
            selectField(newField.id);
        }

        // Duplicate Field
        function duplicateField(fieldId, event) {
            if (event) event.stopPropagation();
            const source = formFields.find(f => f.id === fieldId);
            if (!source) return;

            const copy = JSON.parse(JSON.stringify(source));
            copy.id = 'f_' + Math.random().toString(36).substr(2, 9);
            copy.key = generateStableKey(source.label + '_copy', source.type);
            copy.label = source.label + ' (Copy)';
            copy.order = formFields.length + 1;

            formFields.push(copy);
            renderCanvas();
            selectField(copy.id);
        }

        // Remove Field
        function removeField(fieldId, event) {
            if (event) event.stopPropagation();
            if (!confirm('Remove this question from the form?')) return;

            formFields = formFields.filter(f => f.id !== fieldId);
            if (selectedFieldId === fieldId) {
                selectedFieldId = formFields.length > 0 ? formFields[0].id : null;
            }
            renderCanvas();
            renderInspector();
        }

        // Reorder Field Up / Down
        function moveField(fieldId, delta, event) {
            if (event) event.stopPropagation();
            const idx = formFields.findIndex(f => f.id === fieldId);
            if (idx < 0) return;
            const targetIdx = idx + delta;
            if (targetIdx < 0 || targetIdx >= formFields.length) return;

            const temp = formFields[idx];
            formFields[idx] = formFields[targetIdx];
            formFields[targetIdx] = temp;

            // Update order sequence
            formFields.forEach((f, i) => f.order = i + 1);

            renderCanvas();
        }

        // Select Field for Inspector
        function selectField(fieldId) {
            selectedFieldId = fieldId;
            renderCanvas();
            renderInspector();
        }

        // Escape HTML to prevent XSS in preview/canvas
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Render Canvas
        function renderCanvas() {
            const container = document.getElementById('fieldsContainer');
            const emptyState = document.getElementById('canvasEmptyState');
            const countBadge = document.getElementById('fieldCountBadge');

            countBadge.textContent = `${formFields.length} questions configured`;

            if (formFields.length === 0) {
                container.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            container.innerHTML = formFields.map((field, idx) => {
                const isSelected = field.id === selectedFieldId;
                const hasCondition = field.condition && field.condition.field;

                return `
                    <div class="field-card ${isSelected ? 'selected' : ''}" onclick="selectField('${field.id}')">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                <span style="font-size: 11px; font-weight: 800; color: var(--muted); background: #f1f5f9; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                    ${idx + 1}
                                </span>
                                <div style="min-width: 0;">
                                    <div style="font-weight: 700; color: var(--ink); font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ${escapeHtml(field.label)}
                                        ${field.required ? '<span style="color: #dc2626;">*</span>' : ''}
                                    </div>
                                    <div style="display: flex; gap: 6px; align-items: center; margin-top: 3px; font-size: 11px;">
                                        <span class="pill" style="font-size: 10px; padding: 1px 6px;">${escapeHtml(field.type)}</span>
                                        <span style="color: var(--muted); font-family: monospace;">key: ${escapeHtml(field.key)}</span>
                                        ${hasCondition ? '<span class="pill" style="background: #fef3c7; color: #b45309; font-size: 10px;">⚡ Conditional</span>' : ''}
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 4px; align-items: center;">
                                <button type="button" title="Move Up" onclick="moveField('${field.id}', -1, event)" style="border: 1px solid var(--line); background: #fff; border-radius: 4px; cursor: pointer; padding: 2px 6px; font-size: 11px;" ${idx === 0 ? 'disabled style="opacity:0.3"' : ''}>↑</button>
                                <button type="button" title="Move Down" onclick="moveField('${field.id}', 1, event)" style="border: 1px solid var(--line); background: #fff; border-radius: 4px; cursor: pointer; padding: 2px 6px; font-size: 11px;" ${idx === formFields.length - 1 ? 'disabled style="opacity:0.3"' : ''}>↓</button>
                                <button type="button" title="Duplicate" onclick="duplicateField('${field.id}', event)" style="border: 1px solid var(--line); background: #fff; border-radius: 4px; cursor: pointer; padding: 2px 6px; font-size: 11px;">📋</button>
                                <button type="button" title="Delete" onclick="removeField('${field.id}', event)" style="border: 1px solid #fecaca; background: #fef2f2; color: #dc2626; border-radius: 4px; cursor: pointer; padding: 2px 6px; font-size: 11px;">✕</button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Render Inspector Panel
        function renderInspector() {
            const container = document.getElementById('inspectorContent');
            const field = formFields.find(f => f.id === selectedFieldId);

            if (!field) {
                container.innerHTML = `
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--muted); letter-spacing: 0.08em; margin-bottom: 6px;">Field Inspector</div>
                    <div style="padding: 24px 12px; text-align: center; color: var(--muted); font-size: 13px;">
                        Select or add any field on the canvas to configure its label, validation, and branching rules.
                    </div>
                `;
                return;
            }

            const otherFields = formFields.filter(f => f.id !== field.id);

            let optionsHtml = '';
            if (field.type === 'select' || field.type === 'radio' || field.type === 'checkbox') {
                const opts = field.options || [];
                optionsHtml = `
                    <div style="border-top: 1px solid var(--line); padding-top: 14px; margin-top: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label style="font-size: 12px; font-weight: 700; color: #475569;">Choices / Options</label>
                            <button type="button" onclick="addOptionToCurrentField()" style="background: transparent; border: 0; color: var(--green); font-size: 12px; font-weight: 700; cursor: pointer;">+ Add Choice</button>
                        </div>
                        <div style="display: grid; gap: 6px;" id="optionsList">
                            ${opts.map((opt, oIdx) => `
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <input type="text" value="${escapeHtml(opt.label)}" placeholder="Choice Label" oninput="updateOptionLabel(${oIdx}, this.value)" style="flex: 1; padding: 6px 10px; border: 1px solid var(--line); border-radius: 6px; font-size: 12px;">
                                    <button type="button" onclick="removeOptionFromCurrentField(${oIdx})" style="border: 0; background: transparent; color: #dc2626; cursor: pointer;">✕</button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            const cond = field.condition || { field: '', operator: 'equals', value: '' };

            container.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--green); letter-spacing: 0.08em;">Editing Field</div>
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--ink);">${escapeHtml(field.type.toUpperCase())}</h4>
                    </div>
                    <span class="pill" style="font-size: 10px;">ID: ${escapeHtml(field.id)}</span>
                </div>

                <div style="display: grid; gap: 12px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569;">Customer Label</label>
                        <input type="text" value="${escapeHtml(field.label)}" oninput="updateCurrentField('label', this.value)" style="width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px; margin-top: 3px;">
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569;">Stable Field Identifier (Key)</label>
                        <input type="text" value="${escapeHtml(field.key)}" oninput="updateCurrentField('key', this.value)" style="width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 6px; font-family: monospace; font-size: 12px; margin-top: 3px;">
                        <span style="font-size: 11px; color: var(--muted);">Stable key used in database & API payload</span>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569;">Placeholder Text</label>
                        <input type="text" value="${escapeHtml(field.placeholder || '')}" oninput="updateCurrentField('placeholder', this.value)" placeholder="e.g. Enter details..." style="width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 6px; font-size: 12px; margin-top: 3px;">
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569;">Help / Guidance Text</label>
                        <input type="text" value="${escapeHtml(field.help_text || '')}" oninput="updateCurrentField('help_text', this.value)" placeholder="Subtext under the question" style="width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 6px; font-size: 12px; margin-top: 3px;">
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <input type="checkbox" id="fieldRequiredCheck" ${field.required ? 'checked' : ''} onchange="updateCurrentField('required', this.checked)" style="width: 16px; height: 16px; accent-color: var(--green);">
                        <label for="fieldRequiredCheck" style="font-size: 13px; font-weight: 600; color: var(--ink); cursor: pointer;">Required Question</label>
                    </div>

                    ${optionsHtml}

                    <!-- Conditional Visibility Rules -->
                    <div style="border-top: 1px solid var(--line); padding-top: 14px; margin-top: 6px;">
                        <label style="font-size: 12px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 6px;">
                            <span>⚡ Conditional Visibility</span>
                        </label>
                        <p style="font-size: 11px; color: var(--muted); margin: 2px 0 8px;">Show this question only when another answer matches.</p>

                        <div style="display: grid; gap: 6px; background: #f8fafc; border: 1px solid var(--line); border-radius: 6px; padding: 10px;">
                            <select onchange="updateConditionField('field', this.value)" style="width: 100%; padding: 6px; border: 1px solid var(--line); border-radius: 4px; font-size: 11.5px;">
                                <option value="">-- Always Show (No condition) --</option>
                                ${otherFields.map(of => `
                                    <option value="${escapeHtml(of.key)}" ${cond.field === of.key ? 'selected' : ''}>
                                        If "${escapeHtml(of.label)}"
                                    </option>
                                `).join('')}
                            </select>

                            ${cond.field ? `
                                <div style="display: flex; gap: 4px;">
                                    <select onchange="updateConditionField('operator', this.value)" style="width: 110px; padding: 6px; border: 1px solid var(--line); border-radius: 4px; font-size: 11px;">
                                        <option value="equals" ${cond.operator === 'equals' ? 'selected' : ''}>Equals</option>
                                        <option value="not_equals" ${cond.operator === 'not_equals' ? 'selected' : ''}>Not Equals</option>
                                        <option value="not_empty" ${cond.operator === 'not_empty' ? 'selected' : ''}>Is Filled</option>
                                    </select>
                                    <input type="text" value="${escapeHtml(cond.value || '')}" placeholder="Target value..." oninput="updateConditionField('value', this.value)" style="flex: 1; padding: 6px; border: 1px solid var(--line); border-radius: 4px; font-size: 11px;">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }

        // Field Property Updates
        function updateCurrentField(prop, value) {
            const field = formFields.find(f => f.id === selectedFieldId);
            if (!field) return;
            field[prop] = value;
            renderCanvas();
        }

        function addOptionToCurrentField() {
            const field = formFields.find(f => f.id === selectedFieldId);
            if (!field) return;
            if (!field.options) field.options = [];
            const count = field.options.length + 1;
            field.options.push({ value: 'opt_' + count, label: 'Option ' + count });
            renderInspector();
        }

        function updateOptionLabel(idx, val) {
            const field = formFields.find(f => f.id === selectedFieldId);
            if (!field || !field.options || !field.options[idx]) return;
            field.options[idx].label = val;
            field.options[idx].value = val.toLowerCase().replace(/[^a-z0-9]+/g, '_');
        }

        function removeOptionFromCurrentField(idx) {
            const field = formFields.find(f => f.id === selectedFieldId);
            if (!field || !field.options) return;
            field.options.splice(idx, 1);
            renderInspector();
        }

        function updateConditionField(prop, val) {
            const field = formFields.find(f => f.id === selectedFieldId);
            if (!field) return;
            if (!field.condition) field.condition = { field: '', operator: 'equals', value: '' };
            field.condition[prop] = val;
            if (!field.condition.field) {
                field.condition = null;
            }
            renderCanvas();
            renderInspector();
        }

        // Live Preview Modal
        function openLivePreviewModal() {
            const modal = document.getElementById('livePreviewModal');
            const container = document.getElementById('previewContainer');
            modal.style.display = 'flex';

            renderLivePreview();
        }

        function closeLivePreviewModal() {
            document.getElementById('livePreviewModal').style.display = 'none';
        }

        // Deterministic Live Preview Engine
        function renderLivePreview() {
            const container = document.getElementById('previewContainer');
            const formName = document.getElementById('formNameInput').value || 'Customer Support Form';

            // Extract simulated values from preview inputs if already entered
            const simValues = {};
            const existingInputs = container.querySelectorAll('[data-preview-key]');
            existingInputs.forEach(el => {
                simValues[el.getAttribute('data-preview-key')] = el.value;
            });

            container.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--ink); margin: 0 0 6px;">${escapeHtml(formName)}</h2>
                    <p style="font-size: 13px; color: var(--muted); margin: 0;">Fill out the fields below. Live conditional branching will toggle visibility automatically.</p>
                </div>

                <div style="display: grid; gap: 16px;">
                    ${formFields.map(field => {
                        // Evaluate condition against current simValues
                        const isVisible = evaluatePreviewCondition(field, simValues);
                        const currVal = simValues[field.key] || field.default_value || '';

                        return `
                            <div id="preview_wrap_${field.key}" style="display: ${isVisible ? 'block' : 'none'};">
                                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                                    ${escapeHtml(field.label)}
                                    ${field.required ? '<span style="color: #dc2626;">*</span>' : ''}
                                </label>
                                ${renderPreviewInputHtml(field, currVal)}
                                ${field.help_text ? `<div style="font-size: 11.5px; color: var(--muted); margin-top: 3px;">${escapeHtml(field.help_text)}</div>` : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        function renderPreviewInputHtml(field, value) {
            const key = escapeHtml(field.key);
            const placeholder = escapeHtml(field.placeholder || '');

            switch (field.type) {
                case 'textarea':
                    return `<textarea data-preview-key="${key}" rows="3" placeholder="${placeholder}" oninput="handlePreviewInputChange('${key}', this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px;">${escapeHtml(value)}</textarea>`;
                
                case 'select':
                    const opts = field.options || [];
                    return `
                        <select data-preview-key="${key}" onchange="handlePreviewInputChange('${key}', this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px;">
                            <option value="">-- Choose Option --</option>
                            ${opts.map(o => `<option value="${escapeHtml(o.label)}" ${value === o.label ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('')}
                        </select>
                    `;

                case 'radio':
                    const rOpts = field.options || [];
                    return `
                        <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 4px;">
                            ${rOpts.map(o => `
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                                    <input type="radio" name="sim_${key}" data-preview-key="${key}" value="${escapeHtml(o.label)}" ${value === o.label ? 'checked' : ''} onchange="handlePreviewInputChange('${key}', this.value)">
                                    <span>${escapeHtml(o.label)}</span>
                                </label>
                            `).join('')}
                        </div>
                    `;

                case 'checkbox':
                    const cOpts = field.options || [];
                    return `
                        <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 4px;">
                            ${cOpts.map(o => `
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                                    <input type="checkbox" name="sim_${key}" value="${escapeHtml(o.label)}" onchange="handlePreviewCheckboxChange('${key}')">
                                    <span>${escapeHtml(o.label)}</span>
                                </label>
                            `).join('')}
                        </div>
                    `;

                case 'file':
                    return `
                        <div style="padding: 16px; border: 2px dashed var(--line); border-radius: 8px; text-align: center; background: #f8fafc; font-size: 12px; color: var(--muted);">
                            <span>📎 Click or drag file to simulate upload (Allowed: ${escapeHtml(field.validation?.allowed_file_types || 'All')})</span>
                        </div>
                    `;

                default:
                    return `<input type="${field.type}" data-preview-key="${key}" value="${escapeHtml(value)}" placeholder="${placeholder}" oninput="handlePreviewInputChange('${key}', this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px;">`;
            }
        }

        function handlePreviewInputChange(key, val) {
            renderLivePreview();
        }

        function handlePreviewCheckboxChange(key) {
            renderLivePreview();
        }

        function evaluatePreviewCondition(field, currentValues) {
            if (!field.condition || !field.condition.field) return true;
            const targetFieldKey = field.condition.field;
            const operator = field.condition.operator || 'equals';
            const targetVal = String(field.condition.value || '').trim();
            const actualVal = String(currentValues[targetFieldKey] || '').trim();

            if (operator === 'equals') {
                return actualVal.toLowerCase() === targetVal.toLowerCase();
            } else if (operator === 'not_equals') {
                return actualVal.toLowerCase() !== targetVal.toLowerCase();
            } else if (operator === 'not_empty') {
                return actualVal !== '';
            }
            return true;
        }

        // Submission & Draft Saving
        function submitForm(status) {
            document.getElementById('formStatusInput').value = status;
            document.getElementById('schemaJsonInput').value = JSON.stringify(formFields);
            document.getElementById('formBuilderForm').submit();
        }

        function confirmPublishForm() {
            document.getElementById('publishConfirmModal').style.display = 'flex';
        }

        function closePublishModal() {
            document.getElementById('publishConfirmModal').style.display = 'none';
        }

        function executePublish() {
            const summary = document.getElementById('publishChangeSummary').value;
            // Inject change summary into form
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'change_summary';
            input.value = summary;
            document.getElementById('formBuilderForm').appendChild(input);

            submitForm('published');
        }
    </script>
@endsection
