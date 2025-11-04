@php($title = $title ?? 'Admin')
        <!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title)</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('admin/images/favicon.png') }}">

    <!-- Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/pg-calendar/css/pignose.calendar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/chartist/css/chartist.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/chartist-plugin-tooltips/css/chartist-plugin-tooltip.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/style.css') }}">
    @stack('styles')
</head>
<body>

<div id="main-wrapper">
    @include('admin.partials.nav')
    @include('admin.partials.header')
    @include('admin.partials.sidebar')

    <div class="content-body">
        <div class="container-fluid mt-3">
            @yield('content')
        </div>
    </div>

    @include('admin.partials.footer')
</div>

<script src="{{ asset('admin/plugins/common/common.min.js') }}"></script>

<!-- Core scripts -->
<script src="{{ asset('admin/js/custom.min.js') }}"></script>
<script src="{{ asset('admin/js/settings.js') }}"></script>
<script src="{{ asset('admin/js/gleek.js') }}"></script>
<script src="{{ asset('admin/js/styleSwitcher.js') }}"></script>

<!-- Plugins cho dashboard -->
<script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('admin/plugins/pg-calendar/js/pignose.calendar.min.js') }}"></script>

<script src="{{ asset('admin/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script src="{{ asset('admin/plugins/chartist/js/chartist.min.js') }}"></script>
<script src="{{ asset('admin/plugins/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js') }}"></script>

<script src="{{ asset('admin/plugins/slimscroll/jquery.slimscroll.js') }}"></script>

<script src="{{ asset('admin/plugins/morris/raphael.js') }}"></script>
<script src="{{ asset('admin/plugins/morris/morris.js') }}"></script>

<script src="{{ asset('admin/plugins/d3/d3.min.js') }}"></script>
<script src="{{ asset('admin/plugins/topojson/topojson.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datamaps/datamaps.world.min.js') }}"></script>

<!-- Dashboard logic -->
<script src="{{ asset('admin/js/dashboard.js') }}"></script>

<!-- Fix menu collapse issue -->
<script>
    $(document).ready(function() {
        // Đảm bảo metisMenu được khởi tạo đúng cách sau khi DOM sẵn sàng
        setTimeout(function() {
            if ($("#menu").length && typeof $.fn.metisMenu !== 'undefined') {
                // Dispose nếu đã được khởi tạo
                try {
                    var instance = $("#menu").data('metisMenu');
                    if (instance) {
                        $("#menu").metisMenu('dispose');
                    }
                } catch(e) {}
                
                // Khởi tạo lại với options rõ ràng
                $("#menu").metisMenu({
                    toggle: true,
                    preventDefault: true
                });
            }
        }, 50);
    });
</script>

@stack('scripts')
</body>
</html>