<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandAuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'success' => 'boolean',
        'executed_at' => 'datetime',
    ];

    public function cliSession()
    {
        return $this->belongsTo(CliSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
