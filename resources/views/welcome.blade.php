@extends('layouts.layout')


@section('content')
    <div class="content page-check">
            <div class="col-lg-12 col-12 text-center">
                <img src="/images/logo.png"/>
            </div>
        <div class="row">
            <div class="col-lg-12 col-sm-12 col-md-12 col-12 text-center">
                <div class="row">
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">

            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">
                <div class="form-check-number">
                    <div class="h2">
                    Информационная база <br/>выполненных поверок
                </div>
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
                            </div>
                            <div class="col-lg-4">
                                <input type="text" class="form-control" name="nmbr" id="nmbr" value="" placeholder="000-00-00000">
                            </div>
                            <div class="col-lg-4">
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
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12">

            </div>
                </div>
            </div>
        </div>
    </div>

@stop
