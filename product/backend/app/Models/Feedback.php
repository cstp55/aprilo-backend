<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'feedback';

    protected $fillable = [
        'organization_id',
        'answer_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
