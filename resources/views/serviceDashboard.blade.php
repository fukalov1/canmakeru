<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="col-lg-4">
    <div class="row">
        <div class="col-lg-12">
            <b>
                Обновление токена
            </b>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            @if(!$YANDEX_CLIENT_ID)
             <span class="h3 danger">
                Заполните данные для доступа к Яндекс.Диску для приложений в Конфигурации сайта
            </span>
            @else
                <input type="hidden" id="clientId" value="{{$YANDEX_CLIENT_ID}}"/>
                {{--<a href="https://oauth.yandex.ru/authorize?response_type=code&client_id={{$YANDEX_CLIENT_ID}}">получить</a>--}}
                <ol>
                    <li>
                        <button type="button" id="getCode">получить код</button>
                    </li>
                    <li>
                        <input type="text" id="code" placeholder="введите код">
                        <button type="button" class="btn btn-success" id="refreshToken">обновить</button>
                    </li>
                </ol>

            @endif
        </div>
    </div>

</div>
<div class="col-lg-4">
    <form action="/admin/export-package-fgis" method="POST" enctype="multipart/form-data">
    @csrf <!-- {{ csrf_field() }} -->
    <div class="row">
        <div class="col-lg-12">
            <b>
                Выгрузка во ФГИС
            </b>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <label>Номер пакета</label>
            <input type="text" name="package_number" value="{{ config('package_number', 1) }}" placeholder="номер пакета"><br/>
            <button type="reset" class="btn btn-default">сбросить</button>
            <button type="submit" class="btn btn-info" id="convertXlsToXML">старт</button>
        </div>
    </div>
    </form>
</div>
<div class="col-lg-4">
    <form action="/admin/convert-xls-xml" method="POST" enctype="multipart/form-data">
    @csrf <!-- {{ csrf_field() }} -->
    <div class="row">
        <div class="col-lg-12">
            <b>
                Конвертация данных о поверках в XML
            </b>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <input type="file" name="file_xls" id="file_xls" placeholder="файл-источник">
            <button type="reset" class="btn btn-default">сбросить</button>
            <button type="submit" class="btn btn-danger" id="convertXlsToXML">конвертировать</button>
        </div>
    </div>
    </form>
</div>
