<?php

namespace App\Http\Requests;

use App\Models\Ban;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $maBan = $this->route('ma_ban');
        if (!is_string($maBan)) {
            return false;
        }

        // Bàn chỉ cần tồn tại — một bàn có thể có nhiều lượt khách/đơn,
        // không chặn khi bàn đang "có khách" để tránh lỗi truy cập khi quét lại.
        return Ban::where('ma_ban', $maBan)->exists();
    }

    protected function failedAuthorization(): never
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Bàn này không tồn tại.'
        );
    }

    public function rules(): array
    {
        return [
            'ten_kh' => 'required|string|max:100',
            'sdt_kh' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_kh.required' => 'Vui lòng nhập tên khách hàng.',
            'ten_kh.max' => 'Tên không được quá 100 ký tự.',
            'sdt_kh.max' => 'Số điện thoại không được quá 20 ký tự.',
            'sdt_kh.regex' => 'Số điện thoại chỉ chứa số, dấu +, dấu -, khoảng trắng hoặc ngoặc.',
        ];
    }
}
