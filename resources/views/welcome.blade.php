@extends('layouts.layout')


@section('content')
    <div class="content page-check">
            <div class="col-lg-12 col-12 text-center">
                <div class="h2">
                    Информационная база выполненных поверок
                </div>
            </div>

            <div class="col-lg-12 col-12 text-center">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        <div class="row">
            <div class="col-lg-12 col-sm-12 col-md-12 col-12 text-center">
                <div class="row">
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">

            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">
                <div class="form-check-number">

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
                                <input type="text" class="form-control" name="id_1" id="id_1" value="" placeholder="">
                            </div>
                            <div class="col-lg-4">
                                <input type="text" class="form-control" name="id_2" id="id_2" value="" placeholder="">
                            </div>
                            <div class="col-lg-4">
                                <input type="text" class="form-control" name="id_3" id="id_3" value="" placeholder="">
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
                            <div class="col-lg-3">
                            </div>
                            <div class="col-lg-6">
                                  <input type="text" class="form-control" name="pin" id="pin" value="" placeholder="">
                            </div>
                            <div class="col-lg-3">
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
                                    <input type="submit" class="btn btn-primary" name="commit" value="Проверить">
                            </div>
                            <div class="col-lg-4">
                            </div>
                        </div>

                    </form>
                </section>
                </div>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">

            </div>
                </div>
            </div>
        </div>
    </div>

{{--    <form method="post" action="/upl.php" enctype="multipart/form-data">--}}
{{--        <input type="hidden" name="appUUID" value="437447dcb8b8"/>--}}
{{--        <input type="text" name="partnerKey" value="0911f8H1n2"/>--}}
{{--        <input type="text" name="id" value="11800409"/>--}}
{{--        <input type="text" name="pin" value="6409"/>--}}
{{--        <input type="text" name="dt" value="2019-11-26 20:13:02"/>--}}
{{--        <input type="file" name="protokol_photo"/>--}}
{{--        <input type="file" name="protokol_photo1"/>--}}
{{--        <input type="file" name="meter_photo"/>--}}
{{--        <input type="submit" value="send"/>--}}
{{--    </form>--}}

@stop
