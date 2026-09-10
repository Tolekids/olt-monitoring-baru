<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_polling_enabled' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function credentials()
    {
        return $this->hasMany(DeviceCredential::class);
    }

    public function ponPorts()
    {
        return $this->hasMany(OltPonPort::class);
    }

    public function trafficSamples()
    {
        return $this->hasMany(TrafficSample::class);
    }

    public function syslogEvents()
    {
        return $this->hasMany(SyslogEvent::class);
    }
}
