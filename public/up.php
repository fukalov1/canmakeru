
<!DOCTYPE html>
<!--[if lt IE 7]><html lang="ru" class="lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html lang="ru" class="lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html lang="ru" class="lt-ie9"><![endif]-->
<!--[if gt IE 8]><!-->
<html lang="ru">
<!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <title>Информационная база выполненных поверок</title>
    <meta name="description" content="Информационная база выполненных поверок" />
    <meta name="keywords" content="поверка счетчиков"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="WtJF9CLajNjmQEDPZxgfsviklqbpMhQpSlzHjbUI">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">



<style>
    .row {
        margin-left: 0px;
        margin-right: 0px;
    }
    #nmbr, #pin {
        text-align: center;
    }
    .myimg {
        width: 100%;
    }
    .page-check {
        padding-top: 3%;
    }
    .form-check-number {
        padding: 10px;
        margin: 0 2%;
        width: 96%;
        background-color: #00acd6;
        border: 2px #0d6aad solid;
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
    }
    .h2 {
        font-size: 1.3rem;
        padding: 10px 0;
    }
    .h3 {
        font-size: 1.1rem;
    }
</style>
</head>
<body>

<?php

include 'config.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Output any connection error
if ($conn->connect_error) {
    die('Error : ('. $conn->connect_errno .') '. $mysqli->connect_error);
}
if (isset($_REQUEST['id'])) {
    $prot_id = floatval($_REQUEST['id']);
} else {
    $prot_id= floatval(str_pad($_REQUEST['id_1'], 3, "0", STR_PAD_LEFT).str_pad($_REQUEST['id_2'], 2, "0", STR_PAD_LEFT).str_pad($_REQUEST['id_3'], 5, "0", STR_PAD_LEFT));

}
echo $prot_id;

$stmt = $conn->prepare("SELECT protokol_num, pin, protokol_photo, meter_photo, protokol_photo1, protokol_dt FROM protokols where protokol_num=? and pin=? order by updated_dt desc");
$stmt->bind_param("ii", $prot_id, $_REQUEST['pin']);
$stmt->execute();
$stmt->bind_result($protokol_num, $pin, $protokol_photo, $meter_photo, $protokol_photo1, $protokol_dt);
if (!$stmt->fetch()) {
    error_log("Protokol with protokol_num: '".$prot_id."' and pin: '".$_REQUEST['pin']."' not found", 0);
    die ("Свидетельство не найдено");

}
$stmt->close();
$conn->close();

$matches = [];
preg_match('/(\d\d\d\d)\-(\d\d)/', $protokol_dt,$matches);
$protokol_formated_num = intval(substr($protokol_num, 0,-7)).'-'.intval(substr($protokol_num, -7,2)).'-'.intval(substr($protokol_num, -5));
$protokol_photo = preg_replace('/photos\//','',$protokol_photo);
$protokol_photo1 = preg_replace('/photos\//','',$protokol_photo1);
$meter_photo = preg_replace('/photos\//','',$meter_photo);

?>

    <div class="col-12 content">

        <div class="row page-check">
            <div class="col-lg-12 col-12 text-center">
                <a href="/" title="назад на главную">
                    <img src="/images/logo.png"/>
                </a>

            </div>
        </div>
        <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12 col-12 text-center">
                <div class="h2">
                    Фотоматериалы поверки № 082-19-01469
                </div>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center">
                <label>Свидетельство</label><br/>
                <a href="/photo/<?=$matches[1]?>/<?=$matches[2]?>/<?=$protokol_photo?>" target="_blank" title="открыть в оригинальном размере">
                    <img class="myimg" src="<?=$matches[1]?>/<?=$matches[2]?>/<?=$protokol_photo?>">
                </a>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center">
                <label>Свидетельство (обратная сторона)</label><br/>
                <a href="/photo/<?=$matches[1]?>/<?=$matches[2]?>/<?=$protokol_photo?>" target="_blank" title="открыть в оригинальном размере">
                    <img class="myimg" src="/photo/<?=$matches[1]?>/<?=$matches[2]?>/<?=$protokol_photo1?>">
                </a>
            </div>
            <div class="col-lg-4 col-sm-4 col-md-4 col-12 text-center">
                <label>Счетчик</label><br/>
                <a href="/photo/<?=$matches[1]?>/<?=$matches[2]?>/<?=$meter_photo?>" target="_blank" title="открыть в оригинальном размере">
                    <img class="myimg" src="/photo/<?=$matches[1]?>/<?=$matches[2]?>/<?=$meter_photo?>">
                </a>
            </div>
                    </div>
        <div class="row">
            <br/>
        </div>

    </div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


<script src="http://pin.poverkadoma.ru/js/jquery.maskedinput.js" type="text/javascript"></script>
<script src="http://pin.poverkadoma.ru/js/main.js"></script>

<!--[if lt IE 9]>
<script src="http://pin.poverkadoma.ru/libs/html5shiv/es5-shim.min.js"></script>
<script src="http://pin.poverkadoma.ru/libs/html5shiv/html5shiv.min.js"></script>
<script src="http://pin.poverkadoma.ru/libs/html5shiv/html5shiv-printshiv.min.js"></script>
<script src="http://pin.poverkadoma.ru/libs/respond/respond.min.js"></script>
<![endif]-->

</body>
</html>
