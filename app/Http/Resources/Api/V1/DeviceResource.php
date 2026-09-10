<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vendor' => $this->vendor,
            'model' => $this->model,
            'device_type' => $this->device_type,
            'management_ip' => $this->management_ip,
            'status' => $this->status,
            'is_polling_enabled' => $this->is_polling_enabled,
            'last_seen_at' => $this->last_seen_at,
            'last_error' => $this->last_error,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
