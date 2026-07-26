<?php

namespace App\Services\Knowledge;

use App\Enums\UserRole;
use App\Models\KnowledgeSource;
use App\Models\User;

class SourceAccessService
{
    public function canAccess(User $user, KnowledgeSource $source): bool
    {
        if ($user->organization_id !== $source->organization_id) {
            return false;
        }

        if ($source->access_scope === 'hr_only') {
            return in_array($user->role, [UserRole::Owner->value, UserRole::HrAdmin->value], true);
        }

        return true;
    }
}
