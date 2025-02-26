<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatronRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required|array',
            'user.status',
            'user.status.value' => 'required|string',
            'user.status.desc' => 'required|string',
            'recode_type',
            'recode_type.value' => 'required|string',
            'recode_type.desc' => 'required|string',
            'primary_id' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'required|string',
            'last_name' => 'required|string',
            'full_name' => 'required|string',
            'user_group',
            'user_group.value' => 'required|string',
            'user_group.desc' => 'required|string',
            'account_type',
            'account_type.value' => 'required|string',
            'account_type.desc' => 'required|string',
            'contact_info',
            'contact_info.address' => 'required|array',
            'contact_info.address.line1' => 'required|string',
            'contact_info.address.city' => 'required|string',
            'contact_info.address.country',
            'contact_info.address.country.value' => 'string',
            'contact_info.address.country.desc' => 'string',
            'contact_info.address.state_province' => 'string',
            'contact_info.address.postal_code' => 'string',
            'contact_info.address.address_note' => 'string',
            'contact_info.address.start_date' => 'string',
            'contact_info.address.address_type' => 'array',
            'contact_info.address.address_type.value' => 'string',
            'contact_info.address.address_type.desc' => 'string',
            'contact_info.address.preferred' => 'boolean',
            'contact_info.address.segment_type' => 'required|string',

            'contact_info.email' => 'required|array',
            'contact_info.email.email_address' => 'required|string',
            'contact_info.email.email_type' => 'required|array',
            'contact_info.email.email_type.value' => 'string',
            'contact_info.email.email_type.desc' => 'string',
            'contact_info.email.email_type.preferred' => 'boolean',
            'contact_info.email.segment_type' => 'required|string',

            'contact_info.phone' => 'required|array',
            'contact_info.phone.phone_number' => 'required|string',
            'contact_info.phone.phone_type' => 'required|array',
            'contact_info.phone.phone_type.value' => 'string',
            'contact_info.phone.phone_type.desc' => 'string',
            'contact_info.phone.preferred' => 'string',
            'contact_info.phone.preferred_sms' => 'string',
            'contact_info.phone.segment_type' => 'string',
            'user_identifier' => 'required|array',
            'user_identifier.value' => 'required|string',
            'user_identifier.status' => 'required|string',
            'user_identifier.id_type',
            'user_identifier.id_type.value' => 'required|string',
            'user_identifier.id_type.desc' => 'required|string',
            'user_identifier.segment_type' => 'required|string',
        ];
    }
}
