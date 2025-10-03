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
use Illuminate\Database\Eloquent\Relations\HasOne;

class Parcel extends Model
{
    use HasFactory;
    use HasUserStamps;
    use LogsActivity;

    protected $fillable = [
        'client_id',
        'provider_id',
        'provider_barcode_id',
        'courier_id',
        'assigned_at',
        'code',
        'stop_code',
        'address_line',
        'latitude',
        'longitude',
        'formatted_address',
        'city',
        'state',
        'postal_code',
        'liquidation_code',
        'liquidation_reference',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'assigned_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('client', function (Builder $builder) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId) {
                $builder->where('client_id', $clientId);
            }
        });

        static::creating(function (Parcel $parcel) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId && ! $parcel->client_id) {
                $parcel->client_id = $clientId;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('parcel')
            ->logOnly([
                'client_id',
                'provider_id',
                'provider_barcode_id',
                'code',
                'stop_code',
                'latitude',
                'longitude',
                'formatted_address',
                'liquidation_code',
                'liquidation_reference',
                'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function providerBarcode(): BelongsTo
    {
        return $this->belongsTo(ProviderBarcode::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ParcelEvent::class);
    }

    public function latestScan(): HasOne
    {
        return $this->hasOne(Scan::class)->latestOfMany();
    }
}
