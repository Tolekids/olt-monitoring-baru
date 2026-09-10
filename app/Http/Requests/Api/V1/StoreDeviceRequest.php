<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('devices.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'vendor' => ['required', Rule::in(['zte', 'mikrotik'])],
            'model' => ['required', 'string', 'max:100'],
            'device_type' => ['required', Rule::in(['olt', 'router'])],
            'management_ip' => ['required', 'ip', 'unique:devices,management_ip'],
            'is_polling_enabled' => ['sometimes', 'boolean'],
            'credentials' => ['nullable', 'array'],
            'credentials.protocol' => ['required_with:credentials', Rule::in(['snmp_v2c', 'snmp_v3', 'ssh', 'telnet', 'routeros_api'])],
            'credentials.port' => ['required_with:credentials', 'integer', 'between:1,65535'],
            'credentials.username' => ['nullable', 'string', 'max:255'],
            'credentials.password' => ['nullable', 'string', 'max:1000'],
            'credentials.community' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
