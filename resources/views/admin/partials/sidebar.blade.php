<div class="nk-sidebar">
    <div class="nk-nav-scroll">
        <ul class="metismenu" id="menu">
            <li class="nav-label">Dashboard</li>
            <li>
                <a href="{{ url('/admin') }}" aria-expanded="false">
                    <i class="icon-speedometer"></i><span class="nav-text">Home</span>
                </a>
            </li>

            <li class="nav-label">Management</li>
            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="icon-grid"></i><span class="nav-text">Tours</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="#">All Tours</a></li>
                    <li><a href="#">Create Tour</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="icon-doc"></i><span class="nav-text">Hợp đồng</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.contracts.index') }}">Danh sách</a></li>
                    <li><a href="{{ route('admin.contracts.create') }}">Tạo mới</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="icon-people"></i><span class="nav-text">Users</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="#">All Users</a></li>
                    <li><a href="#">Roles</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>