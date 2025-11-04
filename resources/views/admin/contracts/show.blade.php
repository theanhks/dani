@extends('admin.layouts.app')
@section('title', 'Chi tiết hợp đồng')

@section('content')
<div class="row page-titles mx-0">
    <div class="col p-md-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.contracts.index') }}">Hợp đồng</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Chi tiết</a></li>
        </ol>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Chi tiết hợp đồng</h4>
                        <div>
                            <a href="{{ route('admin.contracts.edit', $contract->id) }}" class="btn btn-warning">
                                <i class="icon-pencil"></i> Sửa
                            </a>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">
                                <i class="icon-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="200">ID</th>
                                    <td>{{ $contract->id }}</td>
                                </tr>
                                <tr>
                                    <th>Tên đơn vị</th>
                                    <td>{{ $contract->company_name }}</td>
                                </tr>
                                <tr>
                                    <th>Số hợp đồng</th>
                                    <td>{{ $contract->contract_number }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày hợp đồng</th>
                                    <td>{{ $contract->contract_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Hiệu lực hợp đồng</th>
                                    <td>{{ $contract->effective_period_value }} {{ $contract->effective_period_type == 'day' ? 'Ngày' : 'Tháng' }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày hết hiệu lực</th>
                                    <td>{{ $contract->expiration_date ? $contract->expiration_date->format('d/m/Y') : 'Vô thời hạn' }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td>{{ $contract->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày cập nhật</th>
                                    <td>{{ $contract->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

