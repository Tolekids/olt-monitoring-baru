<?php

namespace App\Http\Requests\Api\V1;

use App\Domain\RemoteCli\Rules\AllowedCommandRule;
use Illuminate\Foundation\Http\FormRequest;

class ExecuteCliCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cli.open') ?? false;
    }

    public function rules(): array
    {
        return [
            'command' => ['required', 'string', 'max:1000', new AllowedCommandRule()],
        ];
    }
}
