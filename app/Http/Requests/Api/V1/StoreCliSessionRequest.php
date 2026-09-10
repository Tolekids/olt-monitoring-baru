<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCliSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cli.open') ?? false;
    }

    public function rules(): array
    {
        return [
            'protocol' => ['required', Rule::in(['ssh', 'telnet'])],
        ];
    }
}
