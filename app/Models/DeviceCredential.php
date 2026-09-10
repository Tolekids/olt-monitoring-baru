<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCredential extends Model
{
    protected $guarded = ['id'];

    protected $hidden = [
        'encrypted_password',
        'encrypted_community',
    ];

    protected $casts = [
        'encrypted_password' => 'encrypted',
        'encrypted_community' => 'encrypted',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
