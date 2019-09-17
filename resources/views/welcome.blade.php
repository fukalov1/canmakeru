@extends('layouts.layout')


@section('content')
    <div class="content">

        <div class="row page-check">
            <div class="col-lg-12 col-12 text-center">
                <img src="/images/logo.png"/>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">

            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center form-check">
                <div class="h2">
                    Информационная база <br/>выполненных поверок
                </div>
{{--                <div class="h3">--}}
{{--                    Введите № свидетельства и PIN-код--}}
{{--                </div>--}}
                <section class="container">
                    <form method="post" action="/show_result">
                        {{csrf_field()}}
                        <div class="row">
                            <div class="col-lg-12">
                                <label>
                                    Номер свидетельства
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
{{--                                <input type="text" name="nmbr1" class="form-control" maxlength=3 value="" placeholder="№">--}}
                            </div>
                            <div class="col-lg-4">
                                <input type="text" class="form-control" name="nmbr" id="nmbr" value="" placeholder="000-00-00000">

                                {{--                                <input type="text" name="nmbr2" class="form-control"  maxlength=2 value="" placeholder="">--}}
                            </div>
                            <div class="col-lg-4">
{{--                                <input type="text" name="nmbr3" class="form-control"  maxlength=5 value="" placeholder="">--}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>
                                    PIN-код
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                            </div>
                            <div class="col-lg-4">
                                  <input type="text" class="form-control" name="pin" id="pin" value="" placeholder="0000">
                            </div>
                            <div class="col-lg-4">
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                            </div>
                            <div class="col-lg-4 text-center">
                                    <input type="submit" class="btn btn-danger" name="commit" value="Проверить">
                            </div>
                            <div class="col-lg-4">
                            </div>
                        </div>

                    </form>
                </section>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">

            </div>
        </div>


        {{--<form action="/upl.php" method="post" enctype="multipart/form-data">--}}
        {{--<input type="hidden" name="appUUID" value="437447dcb8b8"/>--}}
        {{--<input type="text" name="partnerKey" value="0911f8H1n2"/>--}}
        {{--<input type="file" name="protokol_photo">--}}
        {{--<input type="file" name="protokol_photo1">--}}
        {{--<input type="file" name="meter_photo">--}}
        {{--{{ csrf_field() }}--}}
        {{--<input type="submit" value="upload">--}}
        {{--</form>--}}
    </div>

@stop
