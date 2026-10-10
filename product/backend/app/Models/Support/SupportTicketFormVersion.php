<?php

namespace App\Models\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketFormVersion extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'form_id',
        'organization_id',
        'version',
        'schema',
        'change_summary',
        'published_by',
        'created_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'schema' => 'array',
        'created_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(SupportTicketForm::class, 'form_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
