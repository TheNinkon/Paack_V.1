<?php

namespace App\Models;

use App\Models\Concerns\HasUserStamps;
use App\Support\Activitylog\Concerns\LogsActivity;
use App\Support\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderBarcode extends Model
{
    use HasFactory;
    use HasUserStamps;
    use LogsActivity;

    protected $fillable = [
        'provider_id',
        'label',
        'pattern_regex',
        'sample_code',
        'priority',
        'active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('provider_barcode')
            ->logOnly(['provider_id', 'label', 'pattern_regex', 'sample_code', 'priority', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
