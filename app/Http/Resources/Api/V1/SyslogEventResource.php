<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyslogEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'source_ip' => $this->source_ip,
            'facility' => $this->facility,
            'severity' => $this->severity,
            'message' => $this->message,
            'received_at' => $this->received_at,
        ];
    }
}
