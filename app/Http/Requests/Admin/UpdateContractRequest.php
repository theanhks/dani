<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $contractId = $this->route('contract');
        
        return [
            'company_name' => 'required|string|max:255',
            'contract_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contracts', 'contract_number')->ignore($contractId)
            ],
            'contract_date' => 'required|date',
            'effective_period_value' => 'required|integer|min:1',
            'effective_period_type' => 'required|in:day,month',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Tên đơn vị không được để trống',
            'contract_number.required' => 'Số hợp đồng không được để trống',
            'contract_number.unique' => 'Số hợp đồng đã tồn tại',
            'contract_date.required' => 'Ngày hợp đồng không được để trống',
            'contract_date.date' => 'Ngày hợp đồng không hợp lệ',
            'effective_period_value.required' => 'Giá trị hiệu lực không được để trống',
            'effective_period_value.integer' => 'Giá trị hiệu lực phải là số nguyên',
            'effective_period_value.min' => 'Giá trị hiệu lực phải lớn hơn 0',
            'effective_period_type.required' => 'Loại hiệu lực không được để trống',
            'effective_period_type.in' => 'Loại hiệu lực phải là "Ngày" hoặc "Tháng"',
        ];
    }
}
