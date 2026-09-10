<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyslogEvent extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'received_at' => 'datetime',
        'parsed_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
