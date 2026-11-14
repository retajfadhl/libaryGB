<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            "name" => "sometimes|string|max:255",
            "phone_number" => ["sometimes", "string", "max:10", Rule::unique('customers', 'phone_number')->ignore($customerId)],
            "address" => "sometimes|string|max:255",
            "password" => "sometimes|min:8|string",
            "balance" => "sometimes|integer"
        ];
    }
}
