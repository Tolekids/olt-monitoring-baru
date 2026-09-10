<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OltPonPort extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rx_power_threshold' => 'decimal:3',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }
}
