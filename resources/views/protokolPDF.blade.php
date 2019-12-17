<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>МС-РЕСУРС</title>
</head>
<body>
    <h1>Общество с ограниченной ответственностью "МС-Ресурс"</h1>
    <h1>Результаты поверки № {{ $protokol_num }} от {{ $protokol_dt }}</h1>
    <h4>Свидетельство (лицевая сторона) </h4>
    <img src="{{ asset('/photo/'.$protokol_photo) }}">
{{--    <h4>Свидетельство (обратная сторона) </h4>--}}
{{--    <img src="http://pin.poverkadoma.ru/photo/{{ $protokol_photo1 }}">--}}
{{--    <h4>Фото счетчика </h4>--}}
{{--    <img src="http://pin.poverkadoma.ru/photo/{{ $meter_photo }}">--}}
</body>
</html>
