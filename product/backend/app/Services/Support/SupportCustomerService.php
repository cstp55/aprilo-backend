<?php

namespace App\Services\Support;

use App\Models\Organization;
use App\Models\Support\SupportChannelIdentity;
use App\Models\Support\SupportCustomer;
use Illuminate\Support\Facades\DB;

class SupportCustomerService
{
    /**
     * Find or create a support customer by channel and identifier.
     */
    public function findOrCreateCustomer(
        Organization $organization,
        string $channel,
        string $identifier,
        array $attributes = []
    ): SupportCustomer {
        return DB::transaction(function () use ($organization, $channel, $identifier, $attributes) {
            // 1. Look up existing channel identity
            $identity = SupportChannelIdentity::where('organization_id', $organization->id)
                ->where('channel', $channel)
                ->where('channel_identifier', $identifier)
                ->first();

            if ($identity && $identity->customer) {
                // Update customer attributes if new info provided
                if (! empty($attributes['name']) && $identity->customer->name === 'Anonymous Visitor') {
                    $identity->customer->update(['name' => $attributes['name']]);
                }
                return $identity->customer;
            }

            // 2. Look up customer by email or phone if provided
            $customer = null;
            if (! empty($attributes['email'])) {
                $customer = SupportCustomer::where('organization_id', $organization->id)
                    ->where('email', $attributes['email'])
                    ->first();
            }

            if (! $customer && ! empty($attributes['phone'])) {
                $customer = SupportCustomer::where('organization_id', $organization->id)
                    ->where('phone', $attributes['phone'])
                    ->first();
            }

            // 3. Create customer if not found
            if (! $customer) {
                $customer = SupportCustomer::create([
                    'organization_id' => $organization->id,
                    'name' => $attributes['name'] ?? 'Support Customer',
                    'email' => $attributes['email'] ?? ($channel === 'email' ? $identifier : null),
                    'phone' => $attributes['phone'] ?? ($channel === 'whatsapp' ? $identifier : null),
                    'company' => $attributes['company'] ?? null,
                    'metadata' => $attributes['metadata'] ?? null,
                ]);
            }

            // 4. Attach channel identity
            SupportChannelIdentity::firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'channel' => $channel,
                    'channel_identifier' => $identifier,
                ],
                [
                    'customer_id' => $customer->id,
                    'metadata' => $attributes['metadata'] ?? null,
                ]
            );

            return $customer;
        });
    }
}
