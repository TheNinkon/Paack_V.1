<?php

namespace App\Support\Activitylog\Concerns;

use App\Models\ActivityLog;
use App\Support\Activitylog\LogOptions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model): void {
            $model->recordActivity('created');
        });

        static::updated(function (Model $model): void {
            $model->recordActivity('updated');
        });

        static::deleted(function (Model $model): void {
            $model->recordActivity('deleted');
        });
    }

    protected function recordActivity(string $event): void
    {
        if (! method_exists($this, 'getActivitylogOptions')) {
            return;
        }

        /** @var LogOptions $options */
        $options = $this->getActivitylogOptions();
        $attributesToLog = $this->prepareLoggedAttributes($options, $event);

        if (empty($attributesToLog) && ! $options->submitEmptyLogs) {
            return;
        }

        ActivityLog::create([
            'log_name' => $options->logName,
            'description' => $this->activityDescription($event),
            'event' => $event,
            'subject_type' => static::class,
            'subject_id' => $this->getKey(),
            'causer_id' => $this->resolveCauserId(),
            'properties' => $attributesToLog,
        ]);
    }

    protected function activityDescription(string $event): string
    {
        $modelName = class_basename(static::class);

        return sprintf('%s %s', $modelName, $event);
    }

    protected function resolveCauserId(): ?int
    {
        $user = Auth::user();

        return $user instanceof Authenticatable ? $user->getAuthIdentifier() : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareLoggedAttributes(LogOptions $options, string $event): array
    {
        $attributes = [];

        $attributeKeys = $options->attributes ?: array_keys($this->getAttributes());

        foreach ($attributeKeys as $key) {
            if (! array_key_exists($key, $this->getAttributes())) {
                continue;
            }

            $original = $this->getOriginal($key);
            $current = $this->getAttribute($key);

            if ($event === 'updated' && $options->logOnlyDirty && $original === $current) {
                continue;
            }

            if ($event === 'updated' && $options->logOnlyDirty) {
                $attributes[$key] = [
                    'old' => $original,
                    'new' => $current,
                ];
            } else {
                $attributes[$key] = $current;
            }
        }

        return $attributes;
    }
}
