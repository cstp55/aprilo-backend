<?php

namespace App\Models\Support;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SupportPortalSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'portal_title',
        'portal_subheading',
        'portal_slug',
        'support_email',
        'primary_color',
        'secondary_color',
        'logo_url',
        'favicon_url',
        'default_form_id',
        'allow_anonymous_tickets',
        'require_email_verification',
        'enable_customer_portal_auth',
        'is_portal_active',
        'branding_metadata',
    ];

    protected $casts = [
        'allow_anonymous_tickets' => 'boolean',
        'require_email_verification' => 'boolean',
        'enable_customer_portal_auth' => 'boolean',
        'is_portal_active' => 'boolean',
        'branding_metadata' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function defaultForm(): BelongsTo
    {
        return $this->belongsTo(SupportTicketForm::class, 'default_form_id');
    }

    /**
     * Get or generate stable portal slug based on organization name.
     */
    public function getEffectiveSlug(): string
    {
        if (! empty($this->portal_slug)) {
            return $this->portal_slug;
        }

        $baseSlug = Str::slug($this->organization->name ?? 'support');
        return $baseSlug ?: substr($this->organization_id, 0, 8);
    }

    /**
     * Compute full public support portal URL.
     */
    public function getPublicPortalUrl(): string
    {
        $appUrl = config('services.support.app_url', env('APRILO_SUPPORT_APP_URL', 'https://support.apriloinfotech.com'));
        $slug = $this->getEffectiveSlug();

        return rtrim($appUrl, '/') . '/portal/' . $slug;
    }
}
