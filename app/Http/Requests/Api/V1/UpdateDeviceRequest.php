<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('devices.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'model' => ['sometimes', 'string', 'max:100'],
            'device_type' => ['sometimes', Rule::in(['olt', 'router'])],
            'management_ip' => ['sometimes', 'ip', Rule::unique('devices', 'management_ip')->ignore($this->route('device'))],
            'is_polling_enabled' => ['sometimes', 'boolean'],
            'credentials' => ['sometimes', 'array'],
            'credentials.protocol' => ['required_with:credentials', Rule::in(['snmp_v2c', 'snmp_v3', 'ssh', 'telnet', 'routeros_api'])],
            'credentials.port' => ['required_with:credentials', 'integer', 'between:1,65535'],
            'credentials.username' => ['nullable', 'string', 'max:255'],
            'credentials.password' => ['nullable', 'string', 'max:1000'],
            'credentials.community' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
