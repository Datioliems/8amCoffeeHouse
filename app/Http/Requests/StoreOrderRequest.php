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
            'ten_kh' => 'nullable|string|max:100',
            'sdt_kh' => 'nullable|string|max:15|regex:/^[0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_kh.max' => 'Tên không được quá 100 ký tự.',
            'sdt_kh.max' => 'Số điện thoại không được quá 15 số.',
            'sdt_kh.regex' => 'Số điện thoại chỉ chứa chữ số.',
        ];
    }
}
