<?php

namespace App\Models;

use App\Models\Concerns\HasUserStamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelEvent extends Model
{
    use HasFactory;
    use HasUserStamps;

    protected $fillable = [
        'scan_id',
        'parcel_id',
        'code',
        'event_type',
        'description',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
