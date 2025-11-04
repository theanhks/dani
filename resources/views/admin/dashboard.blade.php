@extends('admin.layouts.app')
@section('title','Dashboard')

@section('content')
        <div class="row">
                <div class="col-lg-12">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">World Map</h4>
                                        <div id="world-map" style="height:400px;"></div>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row">
                <div class="col-lg-12">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Morris Bar Chart</h4>
                                        <div id="morris-bar-chart" style="height:300px;"></div>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row">
                <div class="col-lg-6">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Chart.js Line A</h4>
                                        <canvas id="chartjs_widget_2"></canvas>
                                </div>
                        </div>
                </div>
                <div class="col-lg-6">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Chart.js Line B</h4>
                                        <canvas id="chartjs_widget_3"></canvas>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row">
                <div class="col-lg-6">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Chartist Line</h4>
                                        <div id="chartist_line"></div>
                                </div>
                        </div>
                </div>
                <div class="col-lg-6">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Chartist Donut</h4>
                                        <div id="chartist_pie"></div>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row">
                <div class="col-lg-6">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Todo List</h4>
                                        <input type="text" class="tdl-new form-control" placeholder="Add new task...">
                                        <div class="tdl-content mt-3"><ul></ul></div>
                                </div>
                        </div>
                </div>

                <div class="col-lg-6">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Activity</h4>
                                        <div id="activity" style="height:390px; overflow:hidden;">
                                                <p class="mb-2">Sample activity line 1…</p>
                                                <p class="mb-2">Sample activity line 2…</p>
                                                <p class="mb-2">Sample activity line 3…</p>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>

        <div class="row">
                <div class="col-lg-12">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title">Calendar</h4>
                                        <div class="year-calendar"></div>
                                </div>
                        </div>
                </div>
        </div>
@endsection