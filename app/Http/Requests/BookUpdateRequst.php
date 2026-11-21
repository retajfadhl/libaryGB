<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequst extends FormRequest
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
        return [
            "name"         => "sometimes|string|max:255",
            "ISBN"         => ["sometimes", "string", "max:255", Rule::unique("books", "ISBN")->ignore($this->route("book"))],
            "author"       => "sometimes|string|max:255",
            "publisher"    => "sometimes|string|max:255",
            "publish_date" => "sometimes|date",
            "category_id"  => "sometimes|exists:categories,id",
        ];
    }
}
