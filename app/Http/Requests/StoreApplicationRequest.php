<?php

namespace App\Http\Requests;

use App\Models\JobApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_opening_id' => ['nullable', 'integer', 'exists:job_openings,id'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+\-\s().]{6,40}$/'],
            'city' => ['nullable', 'string', 'max:120'],
            'experience' => ['nullable', 'string', Rule::in(JobApplication::EXPERIENCE)],
            'available_from' => ['nullable', 'date', 'after_or_equal:today'],
            'profile_url' => ['nullable', 'url:http,https', 'max:255'],
            'cover_letter' => ['nullable', 'string', 'max:4000'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'resume.required' => 'Please attach your resume or CV.',
            'resume.mimes' => 'Your resume must be a PDF or Word document.',
            'resume.max' => 'Your resume must not be larger than 5 MB.',
            'profile_url.url' => 'Please enter a full link starting with https://',
        ];
    }
}
