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
    <h6 align="center">
        Общество с ограниченной ответственностью "МС-Ресурс"<br/>
        Результаты поверки № {{ $protokol_num }} от {{ $protokol_dt }} г.
    </h6>
    <table width="100%">
        <tr>
            <td width="33%">
                <img src="http://pin.poverkadoma.ru/photo/{{ $protokol_photo }}" width="350">
            </td>
            <td width="33%">
                <img src="http://pin.poverkadoma.ru/photo/{{ $protokol_photo1 }}" width="350">
            </td>
            <td width="33%">
                <img src="http://pin.poverkadoma.ru/photo/{{ $meter_photo }}" width="350">
            </td>
        </tr>
    </table>
</body>
</html>

