@extends('layouts.app')

@section('content')
<div class="col-md-12">
    <div class="row justify-content-center">
        <div class="col-md-12" id="app">
            <h4>
                Личный кабинет партнера
            </h4>


            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Список поверок</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="stat-tab" data-toggle="tab" href="#statistic" role="tab" aria-controls="statisttic" aria-selected="false">Статистика</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Профиль</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="report-tab" data-toggle="tab" href="#report" role="tab" aria-controls="report" aria-selected="false">Отчеты</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <data-grid/>
                </div>
                <div class="tab-pane fade" id="statistic" role="tabpanel" aria-labelledby="statistic-tab">
                    <div class="statistic">
                        <statistic :height="300"/>
                    </div>
                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <profile-user/>
                </div>
                <div class="tab-pane fade" id="report" role="tabpanel" aria-labelledby="report-tab">4.</div>
            </div>

        </div>
    </div>
</div>
@endsection
