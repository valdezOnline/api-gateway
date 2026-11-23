<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationRequest extends FormRequest
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
            // 'id' => 'required|string',
            'allowed_abilities' => 'sometimes|array',
            'allowed_abilities.*' => 'string|in:gateway:read,gateway:write',
            'allowedAbilities' => 'sometimes|array',
            'allowedAbilities.*' => 'string|in:gateway:read,gateway:write',
            'data.attributes.allowedAbilities' => 'sometimes|array',
            'data.attributes.allowedAbilities.*' => 'string|in:gateway:read,gateway:write',
            'data.attributes.allowed_abilities' => 'sometimes|array',
            'data.attributes.allowed_abilities.*' => 'string|in:gateway:read,gateway:write',
            'attributes' => [
                'name' => 'sometimes|string',
                'description' => 'sometimes|string',
                'createdBy' => 'sometimes|string',
                'apikey' => 'sometimes|string',
                'revokeApiKey' => 'sometimes|boolean',
                'status' => 'sometimes|boolean',
            ]
        ];
    }

    public function mappedAttributes()
    {
        $attributeMap = [
            'data.attributes.name' => 'name',
            'data.attributes.description' => 'description',
            'data.attributes.createdBy' => 'created_by',
            'data.attributes.apikey' => 'apikey',
            'data.attributes.allowedAbilities' => 'allowed_abilities',
            'data.attributes.allowed_abilities' => 'allowed_abilities',
            'data.attributes.status' => 'status',
            'data.attributes.createdAt' => 'created_at',
            'data.attributes.updatedAt' => 'updated_at',
        ];

        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            # code...
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }
        // dd($attributesToUpdate);
        return $attributesToUpdate;
    }
}