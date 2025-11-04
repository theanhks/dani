@php
    $contract = $contract ?? null;
    $isEdit = isset($contract) && $contract !== null;
    $contractId = $isEdit ? $contract->id : null;
    $formAction = $isEdit ? route('admin.contracts.update', $contractId) : route('admin.contracts.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
    
    // Helper function để get value (old() hoặc contract value)
    $getValue = function($field, $default = null) use ($contract) {
        return old($field, $contract->$field ?? $default);
    };
    
    $getDateValue = function($field) use ($contract) {
        if (old($field)) {
            return old($field);
        }
        if (isset($contract) && $contract !== null && isset($contract->$field) && $contract->$field) {
            return $contract->$field->format('Y-m-d');
        }
        return '';
    };
@endphp

<div class="basic-form">
    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="form-group">
            <label>Tên đơn vị <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('company_name') is-invalid @enderror" 
                   name="company_name" 
                   value="{{ $getValue('company_name') }}" 
                   placeholder="Nhập tên đơn vị" 
                   required>
            @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Số hợp đồng <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('contract_number') is-invalid @enderror" 
                   name="contract_number" 
                   value="{{ $getValue('contract_number') }}" 
                   placeholder="Nhập số hợp đồng" 
                   required>
            @error('contract_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Ngày hợp đồng <span class="text-danger">*</span></label>
            <input type="date" 
                   class="form-control @error('contract_date') is-invalid @enderror" 
                   name="contract_date" 
                   value="{{ $getDateValue('contract_date') }}" 
                   required>
            @error('contract_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Hiệu lực hợp đồng <span class="text-danger">*</span></label>
            <div class="row">
                <div class="col-md-6">
                    <input type="number" 
                           class="form-control @error('effective_period_value') is-invalid @enderror" 
                           name="effective_period_value" 
                           value="{{ $getValue('effective_period_value') }}" 
                           placeholder="Nhập số" 
                           min="1"
                           required>
                    @error('effective_period_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <select class="form-control @error('effective_period_type') is-invalid @enderror" 
                            name="effective_period_type" 
                            required>
                        <option value="">Chọn loại</option>
                        <option value="day" {{ $getValue('effective_period_type') == 'day' ? 'selected' : '' }}>Ngày</option>
                        <option value="month" {{ $getValue('effective_period_type') == 'month' ? 'selected' : '' }}>Tháng</option>
                    </select>
                    @error('effective_period_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Cập nhật' : 'Lưu' }}</button>
        </div>
    </form>
</div>

