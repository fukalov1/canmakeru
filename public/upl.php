<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include 'config.php';
header('Content-Type: text/plain; charset=utf-8');

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    die("Загрузка невозможна");
}

// обрезаем пробелы с краев в параметрах
foreach ($_POST as $key => $value) {
    $_POST[$key] = trim($value);
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
//    throw new RuntimeException('Error : ('. $conn->connect_errno .') ');
    die("Соединение с БД не установлено. ".$conn->connect_errno);
}

$siType = $_POST['siType']; // - тип СИ
$waterType = $_POST['waterType']; //- Тип воды
$regNumber = $_POST['regNumber']; // - регистрационный номер
$serialNumber = $_POST['serialNumber']; // - заводской номер
$checkInterval = $_POST['checkInterval']; // - интервал поверки
$checkMethod = $_POST['checkMethod']; // - методика поверки

$stmt = $conn->prepare("select id cust_id FROM customers where code=? and enabled=1");
$stmt->bind_param("s", $_POST['partnerKey']);
$stmt->execute();
$stmt->bind_result($cust_id);

if (!$stmt->fetch()) {
    error_log("customer '".$_POST['partnerKey']."' not found", 0);
    wrileLog('0',"customer '".$_POST['partnerKey']."' not found");
    die("Партнер ".$_POST['partnerKey']." не найден");
}

if (empty($siType)||empty($waterType)||empty($regNumber)||empty($serialNumber)||empty($checkInterval)||empty($checkMethod)) {
    die("Не загружены дополнительные данные о поверке (тип СИ,Тип воды,регистрационный номер,заводской номер,методика поверки)");
}

$stmt->close();

$uid = uniqid();

$uploaddir = 'photos/temp/';
$p_photo = 'protokol_'.$uid.'.jpg';
$p_photo1 = 'protokol1_'.$uid.'.jpg';
$m_photo = 'meter_'.$uid.'.jpg';

if (empty($p_photo)||empty($p_photo1)||empty($m_photo)) {
    die("Не все фото загружены");
}


if (file_exists($p_photo)) {
    unlink($p_photo);
}

if (!move_uploaded_file($_FILES['protokol_photo']['tmp_name'], $uploaddir.$p_photo)) {
    wrileLog($_POST['partnerKey'],"Can not upload protokol photo ".$_FILES['protokol_photo']['tmp_name']);
    die("Ошибка загрузки фото 1 свидетельства. Файл ".$_FILES['protokol_photo']['tmp_name']." не найден.");
}

if (file_exists($p_photo1)) {
    unlink($p_photo1);
}

if (!move_uploaded_file($_FILES['protokol_photo1']['tmp_name'], $uploaddir.$p_photo1)) {
    wrileLog($_POST['partnerKey'],"Can not upload protokol photo 1 ".$_FILES['protokol_photo']['tmp_name']);
    die("Ошибка загрузки фото 2 свидетельства. Файл ".$_FILES['protokol_photo1']['tmp_name']." не найден.");
}


if (file_exists($m_photo)) {
    unlink($m_photo);
}
if (!move_uploaded_file($_FILES['meter_photo']['tmp_name'], $uploaddir.$m_photo)) {
    wrileLog($_POST['partnerKey'],"Can not upload meter photo ".$_FILES['meter_photo']['tmp_name']);
    die("Ошибка загрузки фото счетчика. Файл ".$_FILES['meter_photo']['tmp_name']." не найден.");
}



$stmt = $conn->prepare('select id from protokols where protokol_num=? and pin=?');
$stmt->bind_param("ii", $_POST['id'], $_POST['pin']);
$stmt->execute();
$exists = false;
if ($stmt->fetch()) {
    $exists = true;
}
$stmt->close();

if ($exists) {
    $message = "Delete customer protokols :".$_POST['id']."\t".$_POST['pin'];
    wrileLog($cust_id, $message);
    error_log($message." ID: ".$cust_id, 0);

    $conn->query('delete from protokols  where  protokol_num='.$_POST['id'].' and pin='.$_POST['pin']);
}

$nextTest = null;
if ((int)$checkInterval > 0) {
    $nextTest = strtotime("+$checkInterval YEAR", strtotime($_POST['dt']));
    $nextTest = strtotime('-1 DAYS', $nextTest);
    $nextTest = date("Y-m-d H:i:s", $nextTest);
}


$message = $_POST['id']."\t".$_POST['pin']."\t$p_photo,$p_photo1,$m_photo, $siType, $waterType, $regNumber, $serialNumber, $checkInterval, $checkMethod, $nextTest";
error_log($message." ID: ".$cust_id, 0);
wrileLog($cust_id, $message);

// получение номера "нулевого" акта
$stmt = $conn->prepare("select id act_id FROM  acts where customer_id=? limit 1");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$stmt->bind_result($act_id);

if (!$stmt->fetch()) {
    error_log("customer '".$_POST['partnerKey']."' not found first act ", 0);
    wrileLog('0',"customer '".$_POST['partnerKey']."' not found");
    die("Партнер ".$_POST['partnerKey']." не найден нулевой акт");
}

$stmt->close();

$stmt = $conn->prepare("INSERT INTO protokols (act_id, protokol_num, pin, protokol_photo, protokol_photo1, meter_photo, customer_id, protokol_dt, lat, lng, siType, waterType, regNumber, serialNumber, checkInterval, checkMethod, nextTest) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiisssisddsssssss", $act_id, $_POST['id'],$_POST['pin'],$p_photo, $p_photo1, $m_photo, $cust_id, $_POST['dt'], $_POST['lat'], $_POST['lng'], $siType, $waterType, $regNumber, $serialNumber, $checkInterval, $checkMethod, $nextTest);
if (!$stmt->execute()) {
    wrileLog($cust_id, mysqli_error($conn));
    die("Error insert record: ". mysqli_error($conn)." INSERT INTO protokols (act_id, protokol_num, pin, protokol_photo, protokol_photo1, meter_photo, customer_id, protokol_dt, lat, lng, siType, waterType, regNumber, serialNumber, checkInterval, checkMethod, nextTest) VALUES ({$act_id}, {$_POST['id']}, {$_POST['pin']},$p_photo, $p_photo1, $m_photo, $cust_id, {$_POST['dt']}, {$_POST['lat']}, {$_POST['lng']}, $siType, $waterType, $regNumber, $serialNumber, $checkInterval, $checkMethod, $nextTest");
}
else {
    wrileLog($cust_id, "INSERT INTO protokols (act_id, protokol_num, pin, protokol_photo, protokol_photo1, meter_photo, customer_id, protokol_dt, lat, lng, siType, waterType, regNumber, serialNumber, checkInterval, checkMethod, nextTest) 
VALUES ({$act_id}, ${$_POST['id']}, {$_POST['pin']},$p_photo, $p_photo1, $m_photo, $cust_id, {$_POST['dt']}, {$_POST['lat']}, {$_POST['lng']}, $siType, $waterType, $regNumber, $serialNumber, $checkInterval, $checkMethod, $nextTest");
}


    $output = `cd ../; php7.2 artisan yandex:export $p_photo`;
    $output = `cd ../; php7.2 artisan yandex:export $p_photo1`;
    $output = `cd ../; php7.2 artisan yandex:export $m_photo`;

//echo $output;
    echo "Ok";

$stmt->close();
$conn->close();


function wrileLog($cust_id, $message) {
    $message = date("y.m.d H:i:s")."\t$cust_id\t$message\n";
    $fh = fopen( '../storage/logs/protokols.log', 'a');
    fwrite($fh, $message);
    fclose($fh);
}

?>
