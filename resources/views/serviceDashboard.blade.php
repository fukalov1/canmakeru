<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="col-lg-12">
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
                        <button type="button" id="refreshToken">обновить</button>
                    </li>
                </ol>

            @endif
        </div>
    </div>

</div>
