<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>МС-РЕСУРС</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
    <div align="center">
        <table width="60%" cellspacing="5" cellpadding="10">
        <tr>
            <td width="25%" valign="top">
                <a href="https://pin.poverkadoma.ru/photo/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/act_{{ $act->number_act }}.jpg">
                    <img src="https://pin.poverkadoma.ru/preview/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/act_{{ $act->number_act }}.jpg">
                </a>
            </td>
            <td width="75%" valign="top">
                AКT<br/>
                выполнения метрологической поверки<br/>
                <p>№ <b>{{ $act->number_act }}</b> от <b>{{ $act->date }}</b></p>
                <br/>

                <p>Примечание:  {{ $act->address }}</p>
                <p>Владелец: {{ $act->customer->name }}</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p>На основании результатов метрологической поверки ИПУ, в количестве <strong>{{ $act->meters()->get()->count() }}</strong>
                признаны <strong>{{ $act->type }}</strong> к дальнейшей эксплуатации.
                <p>
            </td>
        </tr>
        @if($act->meters()->get()->count()>0)
            @foreach($act->meters()->get() as $meter)
        <tr>
            <td width="25%" valign="top">
                <a href="https://pin.poverkadoma.ru/photo/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/{{ $meter->meter_photo }}">
                    <img src="http://pin.poverkadoma.ru/preview/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/{{ $meter->meter_photo }}">
                </a>
            </td>
            <td width="75%" valign="top">
                <p>Модификация: {{ $meter->siType }}</p>
                <p>Заводской номер: {{ $meter->serialNumber }}</p>
                <p>Дата поверки: {{ $meter->protokol_dt }}</p>
                <p>Номер в Госреестре: {{ $meter->regNumber }}</p>
                <p>Методика поверки: {{ $meter->checkMethod }}</p>
                <p>Вода: {{ $meter->waterType }}</p>
                <p>
                    <a href="https://fgis.gost.ru/fundmetrology/cm/results?filter_result_docnum={{ $meter->protokol_num }}" target="_blank">
                        Проверить в ФИФ
                    </a> (в электронном реестре)<br/>
                </p>
            </td>
        </tr>
        @endforeach
        @endif
    </table>
    </div>
</body>
</html>

