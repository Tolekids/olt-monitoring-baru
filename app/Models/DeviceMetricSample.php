<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceMetricSample extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'metric_value' => 'decimal:6',
        'recorded_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
