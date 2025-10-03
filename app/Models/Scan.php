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

class Scan extends Model
{
    use HasFactory;
    use HasUserStamps;
    use LogsActivity;

    protected $fillable = [
        'client_id',
        'parcel_id',
        'provider_id',
        'provider_barcode_id',
        'code',
        'is_valid',
        'context',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'context' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('client', function (Builder $builder) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId) {
                $builder->where('client_id', $clientId);
            }
        });

        static::creating(function (Scan $scan) {
            $clientId = app(ClientContext::class)->clientId();
            if ($clientId && ! $scan->client_id) {
                $scan->client_id = $clientId;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('scan')
            ->logOnly([
                'client_id',
                'provider_id',
                'provider_barcode_id',
                'code',
                'is_valid',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function providerBarcode(): BelongsTo
    {
        return $this->belongsTo(ProviderBarcode::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

}
