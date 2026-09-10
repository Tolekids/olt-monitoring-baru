<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficSample extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'rx_bps' => 'decimal:3',
        'tx_bps' => 'decimal:3',
        'recorded_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
