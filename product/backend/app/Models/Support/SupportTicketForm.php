<?php

namespace App\Models\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicketForm extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'category_id',
        'name',
        'slug',
        'description',
        'status',
        'version',
        'is_default',
        'schema',
        'settings',
        'published_at',
        'archived_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_default' => 'boolean',
        'schema' => 'array',
        'settings' => 'array',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportTicketCategory::class, 'category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SupportTicketFormVersion::class, 'form_id')->orderByDesc('version');
    }

    /**
     * Default canonical schema with foundational fields (Subject, Description, Email).
     */
    public static function getDefaultStarterSchema(): array
    {
        return [
            [
                'id' => 'field_' . Str::random(8),
                'key' => 'customer_name',
                'type' => 'text',
                'label' => 'Full Name',
                'placeholder' => 'Jane Doe',
                'help_text' => 'Enter your contact name',
                'required' => true,
                'order' => 1,
            ],
            [
                'id' => 'field_' . Str::random(8),
                'key' => 'customer_email',
                'type' => 'email',
                'label' => 'Work Email Address',
                'placeholder' => 'jane@example.com',
                'help_text' => 'We will reply with status updates to this email',
                'required' => true,
                'order' => 2,
            ],
            [
                'id' => 'field_' . Str::random(8),
                'key' => 'subject',
                'type' => 'text',
                'label' => 'Subject',
                'placeholder' => 'Brief summary of the issue or question',
                'help_text' => '',
                'required' => true,
                'order' => 3,
            ],
            [
                'id' => 'field_' . Str::random(8),
                'key' => 'description',
                'type' => 'textarea',
                'label' => 'Details / Description',
                'placeholder' => 'Please provide detailed context, error messages, and reproduction steps...',
                'help_text' => 'Include any error codes or recent configuration changes',
                'required' => true,
                'order' => 4,
            ],
            [
                'id' => 'field_' . Str::random(8),
                'key' => 'priority',
                'type' => 'select',
                'label' => 'Severity / Priority',
                'placeholder' => 'Select severity',
                'help_text' => 'Choose Critical if production operations are blocked',
                'required' => false,
                'default_value' => 'normal',
                'options' => [
                    ['value' => 'low', 'label' => 'Low (General Inquiry)'],
                    ['value' => 'normal', 'label' => 'Normal (Minor Issue)'],
                    ['value' => 'high', 'label' => 'High (Major Degradation)'],
                    ['value' => 'urgent', 'label' => 'Urgent / Critical (System Down)'],
                ],
                'order' => 5,
            ],
        ];
    }
}
