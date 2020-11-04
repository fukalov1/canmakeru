@extends('layouts.layout')


@section('content')
    <div class="col-12 content">

        <div class="row page-check">
            <div class="col-lg-12 col-12 text-center">
                <a
                    class="header-link"
                    target="_blank"
                    href="https://fgis.gost.ru/fundmetrology/cm/results?filter_result_docnum={{ $number }}">
                    Посмотреть в Федеральном информационном фонде*
                </a>
                <p>* Внимание. Данные передаются в течении 60 дней</p>
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
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center">
                <label>Свидетельство</label><br/>
                <a href="/photo/{{ $protokol_photo }}" target="_blank" title="открыть в оригинальном размере">
                    <img class="myimg" src="/photo/{{ $protokol_photo }}">
                </a>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center">
                <label>Свидетельство (обратная сторона)</label><br/>
                <a href="/photo/{{ $protokol_photo1 }}" target="_blank" title="открыть в оригинальном размере">
                    <img class="myimg" src="/photo/{{ $protokol_photo1 }}">
                </a>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center">
                <label>Счетчик</label><br/>
                <a href="/photo/{{ $meter_photo }}" target="_blank" title="открыть в оригинальном размере">
                    <img class="myimg" src="/photo/{{ $meter_photo }}">
                </a>
            </div>
            @endif
        </div>
        <div class="row">
            <br/>
        </div>

    </div>

@stop
