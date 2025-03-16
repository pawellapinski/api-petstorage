<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string|max:255',
            'status' => 'required|in:available,pending,sold',
            'tags' => 'nullable|string',
            'photoUrls' => 'nullable|string'
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa zwierzęcia jest wymagana.',
            'name.max' => 'Nazwa zwierzęcia nie może być dłuższa niż 255 znaków.',
            'status.required' => 'Status zwierzęcia jest wymagany.',
            'status.in' => 'Status zwierzęcia musi być jednym z: dostępny, oczekujący, sprzedany.'
        ];
    }
}
