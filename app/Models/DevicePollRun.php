<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevicePollRun extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
