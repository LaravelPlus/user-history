<?php

declare(strict_types=1);

namespace LaravelPlus\UserHistory\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use LaravelPlus\UserHistory\Models\UserActivity;

trait HasUserActivity
{
    /**
     * Get all activities for this model.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(UserActivity::class, 'subject');
    }

    /**
     * Record an activity for this model.
     */
    public function recordActivity(string $action, ?string $description = null, array $properties = [], array $metadata = []): UserActivity
    {
        $description = $description ?? $this->getDefaultActivityDescription($action);

        return $this->activities()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'properties' => $properties,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Record a creation activity.
     */
    public function recordCreated(array $properties = [], array $metadata = []): UserActivity
    {
        return $this->recordActivity('created', null, $properties, $metadata);
    }

    /**
     * Record an update activity.
     */
    public function recordUpdated(array $changes = [], array $properties = [], array $metadata = []): UserActivity
    {
        $properties = array_merge($properties, ['changes' => $changes]);

        return $this->recordActivity('updated', null, $properties, $metadata);
    }

    /**
     * Record a deletion activity.
     */
    public function recordDeleted(array $properties = [], array $metadata = []): UserActivity
    {
        return $this->recordActivity('deleted', null, $properties, $metadata);
    }

    /**
     * Record a restoration activity.
     */
    public function recordRestored(array $properties = [], array $metadata = []): UserActivity
    {
        return $this->recordActivity('restored', null, $properties, $metadata);
    }

    /**
     * Get the default activity description for an action.
     */
    protected function getDefaultActivityDescription(string $action): string
    {
        $modelName = class_basename($this);

        return match ($action) {
            'created' => "Created {$modelName}",
            'updated' => "Updated {$modelName}",
            'deleted' => "Deleted {$modelName}",
            'restored' => "Restored {$modelName}",
            default => ucfirst($action) . " {$modelName}",
        };
    }

    /**
     * Get the name to display for this model in activity logs.
     */
    public function getActivitySubjectName(): string
    {
        if (method_exists($this, 'getActivityName')) {
            return $this->getActivityName();
        }

        if (property_exists($this, 'name')) {
            return $this->name;
        }

        if (property_exists($this, 'title')) {
            return $this->title;
        }

        if (property_exists($this, 'email')) {
            return $this->email;
        }

        return class_basename($this) . ' #' . $this->getKey();
    }

    /**
     * Get the URL for this model in activity logs.
     */
    public function getActivitySubjectUrl(): ?string
    {
        if (method_exists($this, 'getActivityUrl')) {
            return $this->getActivityUrl();
        }

        return null;
    }

    /**
     * Boot the trait and register model events.
     */
    protected static function bootHasUserActivity(): void
    {
        static::created(function ($model): void {
            if (config('user-history.auto_record_events', true)) {
                $model->recordCreated();
            }
        });

        static::updated(function ($model): void {
            if (config('user-history.auto_record_events', true)) {
                $changes = $model->getChanges();
                unset($changes['updated_at']); // Don't track timestamp changes

                if (!empty($changes)) {
                    $model->recordUpdated($changes);
                }
            }
        });

        static::deleted(function ($model): void {
            if (config('user-history.auto_record_events', true)) {
                $model->recordDeleted();
            }
        });

        // Only register restored event if the model uses SoftDeletes
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function ($model): void {
                if (config('user-history.auto_record_events', true)) {
                    $model->recordRestored();
                }
            });
        }
    }
}
