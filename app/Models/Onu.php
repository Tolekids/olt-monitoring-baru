<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onu extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'rx_power' => 'decimal:3',
        'last_seen_at' => 'datetime',
    ];

    public function ponPort()
    {
        return $this->belongsTo(OltPonPort::class, 'olt_pon_port_id');
    }
}
