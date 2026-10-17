<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge([
                'price' => str_replace(',', '', (string) $this->input('price')),
            ]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục cho sản phẩm.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'price.required' => 'Giá sản phẩm không được để trống.',
            'price.numeric' => 'Giá sản phẩm phải là một số hợp lệ.',
            'price.gt' => 'Giá sản phẩm phải lớn hơn 0.',
            'stock.required' => 'Số lượng sản phẩm không được để trống.',
            'stock.integer' => 'Số lượng sản phẩm phải là số nguyên.',
            'stock.min' => 'Số lượng sản phẩm phải lớn hơn hoặc bằng 0.',
            'image.image' => 'Tệp tải lên phải là một hình ảnh.',
            'image.mimes' => 'Ảnh sản phẩm phải có định dạng: jpeg, png hoặc webp.',
            'image.max' => 'Dung lượng ảnh tối đa là 5MB.',
            'description.max' => 'Mô tả không được vượt quá 5000 ký tự.',
        ];
    }
}
