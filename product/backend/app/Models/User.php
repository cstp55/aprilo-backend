<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\HasPermissions;

#[Fillable(['organization_id', 'role_id', 'name', 'username', 'email', 'phone', 'password', 'role', 'status', 'employee_id', 'teams_user_id', 'teams_conversation_id', 'teams_service_url', 'escalation_priority', 'escalation_routing_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPermissions, HasUuids, Notifiable;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function wfhRequests(): HasMany
    {
        return $this->hasMany(WfhRequest::class);
    }

    public function idCard(): HasOne
    {
        return $this->hasOne(IdCard::class);
    }

    public function agentSupport(): HasOne
    {
        return $this->hasOne(AgentSupport::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'escalation_routing_active' => 'boolean',
            'escalation_priority' => 'integer',
        ];
    }
}
