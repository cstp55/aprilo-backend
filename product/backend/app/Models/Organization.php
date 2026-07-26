<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'status', 'plan'];

    public function settings(): HasOne
    {
        return $this->hasOne(OrganizationSetting::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
}
