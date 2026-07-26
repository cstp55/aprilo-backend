<?php

namespace App\Services\Audit;

use App\Models\AuditEvent;
use App\Models\User;

class AuditLogger
{
    public function log(User $actor, string $eventType, ?string $entityType = null, ?string $entityId = null, array $metadata = []): AuditEvent
    {
        return AuditEvent::create([
            'organization_id' => $actor->organization_id,
            'actor_user_id' => $actor->id,
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
        ]);
    }
}
