@extends('admin.layouts.app')
@section('title', 'Sửa hợp đồng')

@section('content')
<div class="row page-titles mx-0">
    <div class="col p-md-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.contracts.index') }}">Hợp đồng</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Sửa</a></li>
        </ol>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Sửa hợp đồng</h4>
                    @include('admin.contracts._form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

