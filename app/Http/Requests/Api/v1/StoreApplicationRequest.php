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
        $maxExpiryDate = now()->addYear()->toDateString();

        return [
            'name' => 'required_without:data.attributes.name|string|max:255',
            'description' => 'required_without:data.attributes.description|string|max:1000',
            'createdBy' => 'required_without:data.attributes.createdBy|string|max:255',
            'apikey' => 'required_without:data.attributes.apikey|string|min:32|max:255',
            'status' => 'sometimes|boolean',
            'apiKeyExpiresAt' => "sometimes|date|after:today|before_or_equal:$maxExpiryDate",
            'selectedServices' => 'sometimes|array|min:1',
            'selectedServices.*' => 'string',
            'data.attributes.name' => 'required_without:name|string|max:255',
            'data.attributes.description' => 'required_without:description|string|max:1000',
            'data.attributes.createdBy' => 'required_without:createdBy|string|max:255',
            'data.attributes.apikey' => 'required_without:apikey|string|min:32|max:255',
            'data.attributes.status' => 'sometimes|boolean',
            'data.attributes.apiKeyExpiresAt' => "sometimes|date|after:today|before_or_equal:$maxExpiryDate",
            'data.attributes.selectedServices' => 'sometimes|array|min:1',
            'data.attributes.selectedServices.*' => 'string',
            'allowed_abilities' => 'sometimes|array',
            'allowed_abilities.*' => 'string|in:gateway:read,gateway:write',
            'allowedAbilities' => 'sometimes|array',
            'allowedAbilities.*' => 'string|in:gateway:read,gateway:write',
            'data.attributes.allowedAbilities' => 'sometimes|array',
            'data.attributes.allowedAbilities.*' => 'string|in:gateway:read,gateway:write',
            'data.attributes.allowed_abilities' => 'sometimes|array',
            'data.attributes.allowed_abilities.*' => 'string|in:gateway:read,gateway:write',
        ];
    }
}