<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    'PENDING',
                    'PAID',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status harus PENDING atau PAID.',
        ];
    }
}
