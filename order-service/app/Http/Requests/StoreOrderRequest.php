<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID wajib diisi.',
            'user_id.integer' => 'User ID harus berupa angka bulat.',
            'user_id.min' => 'User ID harus lebih besar dari nol.',
        ];
    }
}