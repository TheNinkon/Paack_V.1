<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasUserStamps
{
    public static function bootHasUserStamps(): void
    {
        static::creating(function (Model $model): void {
            if ($userId = Auth::id()) {
                if (! $model->getAttribute('created_by')) {
                    $model->setAttribute('created_by', $userId);
                }

                if (! $model->getAttribute('updated_by')) {
                    $model->setAttribute('updated_by', $userId);
                }
            }
        });

        static::updating(function (Model $model): void {
            if ($userId = Auth::id()) {
                $model->setAttribute('updated_by', $userId);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
