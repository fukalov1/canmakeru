@extends('layouts.layout')


@section('content')
    <div class="col-12 content">

        <div class="row page-check">
            <div class="col-lg-12 col-12 text-center">
                <a href="/" title="назад на главную">
                    <img src="/images/logo.png"/>
                </a>

            </div>
        </div>
        <div class="row">
            @if($error=='empty')
                <div class="col-lg-12 col-sm-12 col-md-12 col-12 text-center page-check">
                    <h2>
                        Не найдено результатов проверки по указанным параметрам
                    </h2>
                </div>
            @else
            <div class="col-lg-12 col-sm-12 col-md-12 col-12 text-center">
                <div class="h2">
                    Фотоматериалы поверки № {{ $number }}
                </div>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-4 text-center">
                <label>Свидетельство</label><br/>
                <img src="/preview/{{ $protokol_photo }}">
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-4 text-center">
                <label>Свидетельство (обратная сторона)</label><br/>
                <img src="/preview/{{ $protokol_photo1 }}">
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-4 text-center">
                <label>Счетчик</label><br/>
                <img src="/preview/{{ $meter_photo }}">
            </div>
            @endif
        </div>

    </div>

@stop
