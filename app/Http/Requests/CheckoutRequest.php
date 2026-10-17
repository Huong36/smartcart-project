<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'regex:/^\\+?[0-9]{9,15}$/'],
            'note' => ['nullable', 'string', 'max:1000'],
            'checkout_token' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'Vui lòng nhập địa chỉ nhận hàng.',
            'shipping_address.max' => 'Địa chỉ không quá 1000 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại cần từ 9 đến 15 chữ số, có thể bắt đầu bằng +.',
            'note.max' => 'Ghi chú không quá 1000 ký tự.',
            'checkout_token.required' => 'Vui lòng tải lại trang thanh toán.',
            'checkout_token.uuid' => 'Phiên thanh toán không hợp lệ. Vui lòng tải lại trang.',
        ];
    }
}
