@extends('admin.layouts.app')
@section('title', 'Danh sách hợp đồng')

@section('content')
<div class="row page-titles mx-0">
    <div class="col p-md-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Hợp đồng</a></li>
        </ol>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Danh sách hợp đồng</h4>
                        <div>
                            <form action="{{ route('admin.contracts.check-expired') }}" method="POST" class="d-inline mr-2" onsubmit="return confirm('Bạn có chắc chắn muốn kiểm tra và gửi thông báo hợp đồng hết hạn?');">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="icon-envelope"></i> Kiểm tra hết hạn
                                </button>
                            </form>
                            <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">
                                <i class="icon-plus"></i> Tạo mới
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Search Form -->
                    <div class="mb-3">
                        <form method="GET" action="{{ route('admin.contracts.index') }}" class="form-inline">
                            <div class="form-group mr-2">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Tìm kiếm theo mã hợp đồng hoặc tên đơn vị..." 
                                       value="{{ request('search') }}"
                                       style="min-width: 300px;">
                            </div>
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fa fa-search"></i> Tìm kiếm
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">
                                    <i class="icon-close"></i> Xóa bộ lọc
                                </a>
                            @endif
                            <!-- Preserve sort params when searching -->
                            @if(request('order_by'))
                                <input type="hidden" name="order_by" value="{{ request('order_by') }}">
                            @endif
                            @if(request('sort_by'))
                                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                            @endif
                        </form>
                    </div>

                    <div class="table-responsive">
                        @php
                            $currentOrderBy = request('order_by', 'id');
                            $currentSortBy = request('sort_by', 'desc');
                            
                            // Helper function to generate sort URL
                            $getSortUrl = function($column) use ($currentOrderBy, $currentSortBy) {
                                $sortBy = ($currentOrderBy === $column && $currentSortBy === 'asc') ? 'desc' : 'asc';
                                $queryParams = request()->except(['order_by', 'sort_by']);
                                $queryParams['order_by'] = $column;
                                $queryParams['sort_by'] = $sortBy;
                                return route('admin.contracts.index', $queryParams);
                            };
                            
                            // Helper function to get sort icon
                            $getSortIcon = function($column) use ($currentOrderBy, $currentSortBy) {
                                if ($currentOrderBy !== $column) {
                                    return '<span style="opacity: 0.3; margin-left: 5px; font-size: 12px;">↕</span>';
                                }
                                return $currentSortBy === 'asc' 
                                    ? '<span style="margin-left: 5px; font-size: 12px;">↑</span>' 
                                    : '<span style="margin-left: 5px; font-size: 12px;">↓</span>';
                            };
                        @endphp
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đơn vị</th>
                                    <th>Số hợp đồng</th>
                                    <th>
                                        <a href="{{ $getSortUrl('contract_date') }}" class="text-dark" style="text-decoration: none;">
                                            Ngày hợp đồng {!! $getSortIcon('contract_date') !!}
                                        </a>
                                    </th>
                                    <th>Hiệu lực</th>
                                    <th>
                                        <a href="{{ $getSortUrl('expiration_date') }}" class="text-dark" style="text-decoration: none;">
                                            Hết hiệu lực {!! $getSortIcon('expiration_date') !!}
                                        </a>
                                    </th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                    @php
                                        $isExpired = $contract->expiration_date && $contract->expiration_date->lte(now());
                                    @endphp
                                    <tr class="{{ $isExpired ? 'table-danger' : '' }}">
                                        <td>{{ $contract->id }}</td>
                                        <td>{{ $contract->company_name }}</td>
                                        <td>{{ $contract->contract_number }}</td>
                                        <td>{{ $contract->contract_date->format('d/m/Y') }}</td>
                                        <td>{{ $contract->effective_period_value }} {{ $contract->effective_period_type == 'day' ? 'Ngày' : 'Tháng' }}</td>
                                        <td class="{{ $isExpired ? 'text-danger font-weight-bold' : '' }}">
                                            @if($contract->expiration_date)
                                                {{ $contract->expiration_date->format('d/m/Y') }}
                                                @if($isExpired)
                                                    <span class="badge badge-danger ml-2">Đã hết hạn</span>
                                                @endif
                                            @else
                                                Vô thời hạn
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('admin.contracts.show', $contract->id) }}" 
                                                   class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="icon-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.contracts.edit', $contract->id) }}" 
                                                   class="btn btn-sm btn-warning mr-1" title="Sửa">
                                                    <i class="icon-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.contracts.destroy', $contract->id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                        <i class="icon-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $contracts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

