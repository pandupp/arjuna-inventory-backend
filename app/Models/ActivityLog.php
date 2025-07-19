<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'message',
        'type',
        'read_at',
    ];

    /**
     * An activity log entry can belong to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}