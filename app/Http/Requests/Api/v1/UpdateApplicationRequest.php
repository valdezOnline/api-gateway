<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

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
            'attributes' => [
                'name' => 'sometimes|string',
                'description' => 'sometimes|string',
                'createdBy' => 'sometimes|string',
                'apikey' => 'sometimes|string',
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
            'data.attributes.status' => 'status',
            'data.attributes.createdAt' => 'created_at',
            'data.attributes.updatedAt' => 'updated_at',
        ];

        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            # code...
            if ($this->has($key)) {
                // if apikey - we need to encrypt                
                if ($key === 'data.attributes.apikey') {
                    $attributesToUpdate[$attribute] = Crypt::encrypt($this->input($key));
                } else {
                    $attributesToUpdate[$attribute] = $this->input($key);
                }
            }
        }
        // dd($attributesToUpdate);
        return $attributesToUpdate;
    }
}