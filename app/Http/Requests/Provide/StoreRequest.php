<?php

namespace App\Http\Requests\Provide;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                'string'
            ],
            'product' => [
                'required',
                'string'
            ],
            'status' => [
                'required',
                'string'
            ]
        ];
    }
    public function messages(): array
    {
        return [
            'provider_id.required' => 'Bạn chưa chọn nhà cung cấp',
            'product_id.required' => 'Bạn chưa chọn sản phẩm',
            'status.re.required' => 'Bạn chưa chọn trạng thái'
        ];
    }
}
