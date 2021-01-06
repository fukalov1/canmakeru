<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>МС-РЕСУРС</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        img {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div align="center" style="margin: 0 auto; width: 680px; border: 1px #999999 solid; padding-left: 20px;">
        <table width="100%" cellspacing="5" cellpadding="10">
        <tr>
            <td width="25%" valign="top">
                <a href="https://pin.poverkadoma.ru/photo/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/act_{{ $act->number_act }}.jpg" target="_blank">
                    <img src="https://pin.poverkadoma.ru/preview/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/act_{{ $act->number_act }}.jpg" height="250">
                </a>
            </td>
            <td width="75%" valign="top">
                @if ($act->type!='испорчен')
                    <h3>AКT</h3>
                    выполнения метрологической поверки<br/>
                @else
                    Бланк испорчен
                @endif
                <h3>№ {{ $act->number_act }} от {{ date('d-m-Y', strtotime($act->date)) }}</h3>
                <br/>

                @if ($act->type!='испорчен')
                    <p>Примечание:  {{ $act->address }}</p>
                    <p>Владелец: {{ $act->name }}</p>
                @endif
            </td>
        </tr>
        @if ($act->type!='испорчен')
        <tr>
            <td colspan="2">
                <p>На основании результатов метрологической поверки ИПУ, в количестве <strong>{{ $act->meters()->get()->count() }} шт.</strong>

                    <strong>
                        @if ($act->meters()->get()->count()>1)
                            @if ($act->type=='пригодны')
                                признаны пригодными
                            @else
                                признан пригодным
                            @endif
                        @else
                            @if ($act->type=='непригодны')
                                признаны непригодными
                            @else
                                признан непригодным
                            @endif
                        @endif
                    </strong>
                    к дальнейшей эксплуатации.
                <p>
            </td>
        </tr>
        @if($act->meters()->get()->count()>0)
            @foreach($act->meters()->get() as $meter)
        <tr>
            <td width="25%" valign="top">
                <a href="https://pin.poverkadoma.ru/photo/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/{{ $meter->meter_photo }}" target="_blank">
                    <img src="http://pin.poverkadoma.ru/preview/{{ date('Y', strtotime($act->date)) }}/{{ date('m', strtotime($act->date)) }}/{{ $meter->meter_photo }}" height="250">
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
                    <a href="https://fgis.gost.ru/fundmetrology/cm/results?filter_org_title=%D0%9E%D0%9E%D0%9E%20%22%D0%9C%D0%A1-%D0%A0%D0%95%D0%A1%D0%A3%D0%A0%D0%A1%22&filter_mi_number={{ $meter->serialNumber }}" target="_blank">
                        Проверить в ФИФ
                    </a> (в электронном реестре)<br/>
                </p>
            </td>
        </tr>
        @endforeach
        @endif
        @endif
    </table>
    </div>
</body>
</html>

