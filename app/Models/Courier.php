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

class Courier extends Model
{
    use HasFactory;
    use HasUserStamps;
    use LogsActivity;

    protected $fillable = [
        'client_id',
        'user_id',
        'zone_id',
        'vehicle_type',
        'external_code',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const VEHICLE_TYPES = ['foot', 'bike', 'moto', 'car', 'van'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('courier')
            ->logOnly(['client_id', 'user_id', 'zone_id', 'vehicle_type', 'external_code', 'active'])
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

        static::creating(function (Courier $courier) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId && ! $courier->client_id) {
                $courier->client_id = $clientId;
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function parcels(): HasMany
    {
        return $this->hasMany(Parcel::class);
    }
}
