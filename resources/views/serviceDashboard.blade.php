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
                <button type="button" id="getToken">запустить</button>
            @endif
        </div>
    </div>
</div>
