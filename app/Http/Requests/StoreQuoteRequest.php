<?php

namespace App\Http\Requests;

use App\Models\QuoteRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+\-\s().]{6,40}$/'],
            'project_type' => ['required', 'string', Rule::in(QuoteRequest::PROJECT_TYPES)],
            'project_location' => ['required', 'string', 'max:190'],
            'estimated_budget' => ['nullable', 'string', Rule::in(QuoteRequest::BUDGETS)],
            'preferred_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'project_type.in' => 'Please choose one of the listed project types.',
            'description.min' => 'Please describe your project in at least 20 characters.',
            'document.max' => 'The document must not be larger than 8 MB.',
            'document.mimes' => 'Upload a PDF, Word document, JPG or PNG file.',
        ];
    }
}
