<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'website',
        'country',
        'industry',
        'team_size',
        'timezone',
        'status',
        'plan',
    ];

    public function getSubdomainAttribute(): string
    {
        return strtolower(str_replace(' ', '-', $this->name));
    }

    public function settings(): HasOne
    {
        return $this->hasOne(OrganizationSetting::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function agentSupports(): HasMany
    {
        return $this->hasMany(AgentSupport::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(OrganizationEntitlement::class);
    }

    public function hasEntitlement(string $feature): bool
    {
        return $this->entitlements()
            ->where('feature_key', $feature)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();
    }

    public function supportAgentLimit(): ?int
    {
        return $this->entitlements()
            ->where('feature_key', 'support.agents')
            ->where('status', 'active')
            ->get()
            ->map(fn (OrganizationEntitlement $entitlement) => $entitlement->limits['max_agents'] ?? null)
            ->filter(fn ($limit) => $limit !== null)
            ->min();
    }

    public function hasFeature(string $feature): bool
    {
        $subscriptions = $this->subscriptions()
            ->with(['product', 'plan'])
            ->whereIn('status', ['trialing', 'active'])
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->current_cycle_end && $subscription->current_cycle_end->isPast()) {
                continue;
            }

            $productFeatures = $subscription->product?->features ?? [];
            $planFeatures = $subscription->plan?->features ?? [];
            $productSupportsChat = $subscription->product?->category === 'ai_support';

            if ($productSupportsChat || $this->featureValue($productFeatures, $feature) || $this->featureValue($planFeatures, $feature)) {
                return true;
            }
        }

        return false;
    }

    public function supportsLiveChatAgents(): bool
    {
        $settings = $this->settings;

        return ($settings?->live_chat_enabled ?? true)
            && $this->hasEntitlement('support.agents');
    }

    private function featureValue(array $features, string $feature): bool
    {
        if (($features[$feature] ?? false) === true) {
            return true;
        }

        return in_array($feature, $features, true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function knowledgeSources(): HasMany
    {
        return $this->hasMany(KnowledgeSource::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }

    public function ecommerceConnections(): HasMany
    {
        return $this->hasMany(EcommerceConnection::class);
    }

    public function ecommerceLicenses(): HasMany
    {
        return $this->hasMany(EcommerceLicense::class);
    }
}