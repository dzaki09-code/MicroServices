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
                    'PROCESSING',
                    'COMPLETED',
                    'CANCELLED',
                ]),
            ],
            'payment_status' => [
                'sometimes',
                'string',
                Rule::in(['unpaid', 'paid', 'refunded']),
            ],
        ];
    }
}
