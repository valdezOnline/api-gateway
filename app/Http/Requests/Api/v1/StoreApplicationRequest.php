<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'type' => 'application',
            // 'id' => $this->id,
            'allowed_abilities' => 'sometimes|array',
            'allowed_abilities.*' => 'string|in:gateway:read,gateway:write',
            'allowedAbilities' => 'sometimes|array',
            'allowedAbilities.*' => 'string|in:gateway:read,gateway:write',
            'data.attributes.allowedAbilities' => 'sometimes|array',
            'data.attributes.allowedAbilities.*' => 'string|in:gateway:read,gateway:write',
            'data.attributes.allowed_abilities' => 'sometimes|array',
            'data.attributes.allowed_abilities.*' => 'string|in:gateway:read,gateway:write',
            'attributes' => [
                'name' => 'required|string',
                'description' => 'required|string',
                'created_by' => 'required|string',
                'apikey' => 'required|string|min:32|max:255',
                'status' => 'required|boolean',
            ]
        ];
    }
}