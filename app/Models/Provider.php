<?php

namespace App\Models;

use App\Models\Concerns\HasUserStamps;
use App\Support\Activitylog\Concerns\LogsActivity;
use App\Support\Activitylog\LogOptions;
use App\Support\ClientContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;
    use HasUserStamps;
    use LogsActivity;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('provider')
            ->logOnly(['client_id', 'name', 'slug', 'notes', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::addGlobalScope('client', function (Builder $builder) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId) {
                $builder->where('client_id', $clientId);
            }
        });

        static::creating(function (Provider $provider) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId && ! $provider->client_id) {
                $provider->client_id = $clientId;
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProviderBarcode::class);
    }
}
