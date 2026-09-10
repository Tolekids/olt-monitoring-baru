<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CliSession extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(CommandAuditLog::class);
    }
}
