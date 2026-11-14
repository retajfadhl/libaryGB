<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffUpdateRequest extends FormRequest
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
        $staffId = $this->route('id');

        return [
            "name" => "sometimes|required|string",
            "email" => ["sometimes", "required", "email", Rule::unique('staff', 'email')->ignore($staffId)],
            "password" => "sometimes|required|string|min:8",
            "role" => "sometimes|required|string",
            "phone_number" => ["sometimes", "required", "string", Rule::unique('staff', 'phone_number')->ignore($staffId)]
    
        ];
    }
}
