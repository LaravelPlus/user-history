<?php

declare(strict_types=1);

namespace LaravelPlus\UserHistory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class UserActivity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'user_activities';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'subject_type',
        'subject_id',
        'properties',
        'metadata',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'properties' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include activities of a specific action.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include activities for a specific subject.
     */
    public function scopeForSubject($query, $subjectType, $subjectId = null)
    {
        $query->where('subject_type', $subjectType);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query;
    }

    /**
     * Scope a query to only include activities within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the formatted description with subject information.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        if ($this->subject) {
            return sprintf(
                '%s: %s',
                $this->description,
                $this->subject->getActivitySubjectName()
            );
        }

        return $this->description;
    }

    /**
     * Get the activity icon based on action.
     */
    public function getActivityIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'plus-circle',
            'updated' => 'pencil',
            'deleted' => 'trash',
            'restored' => 'arrow-clockwise',
            'login' => 'sign-in',
            'logout' => 'sign-out',
            'password_changed' => 'key',
            'profile_updated' => 'user-edit',
            default => 'circle',
        };
    }

    /**
     * Get the activity color based on action.
     */
    public function getActivityColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            'login' => 'primary',
            'logout' => 'secondary',
            'password_changed' => 'warning',
            'profile_updated' => 'info',
            default => 'secondary',
        };
    }
}
