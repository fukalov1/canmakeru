<?php

include 'config.php';
header('Content-Type: text/plain; charset=utf-8');

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    die("Загрузка невозможна");
}

if ($_POST["appUUID"] != '437447dcb8b8') {
    die ("Загрузка невозможна");
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Output any connection error
if ($conn->connect_error) {
    throw new RuntimeException('Error : ('. $conn->connect_errno .') ');
}

$stmt = $conn->prepare("select id cust_id FROM customers where code=? and enabled=1");
$stmt->bind_param("s", $_POST['partnerKey']);
$stmt->execute();
$stmt->bind_result($cust_id);

if (!$stmt->fetch()) {
    error_log("customer '".$_POST['partnerKey']."' not found", 0);
    die("Партнер ".$_POST['partnerKey']." не найден");
}

$stmt->close();


$uid = uniqid();
$uid1 = uniqid();
$uid2 = uniqid();

$uploaddir = 'photos/temp/';
$p_photo = 'protokol_'.$uid.'.jpg';
$p_photo1 = 'protokol1_'.$uid1.'.jpg';
$m_photo = 'meter_'.$uid2.'.jpg';

if (empty($p_photo)||empty($p_photo1)||empty($m_photo)) {
    die("Не все фото загружены");
}


if (file_exists($p_photo)) {
    unlink($p_photo);
}

if (!move_uploaded_file($_FILES['protokol_photo']['tmp_name'], $uploaddir.$p_photo)) {
    error_log("Can not upload protokol photo", 0);
    die("Ошибка загрузки фото 1 свидетельства");
}

if (file_exists($p_photo1)) {
    unlink($p_photo1);
}

if (!move_uploaded_file($_FILES['protokol_photo1']['tmp_name'], $uploaddir.$p_photo1)) {
    error_log("Can not upload protokol photo 1", 0);
    die("Ошибка загрузки фото 2 свидетельства");
}


if (file_exists($m_photo)) {
    unlink($m_photo);
}
if (!move_uploaded_file($_FILES['meter_photo']['tmp_name'], $uploaddir.$m_photo)) {
    error_log("Can not upload meter photo", 0);
    die("Ошибка загрузки фото счетчика");
}


//$stmt = $conn->prepare("INSERT INTO protokols (protokol_num, pin, protokol_photo, protokol_photo1, meter_photo, customer_id, protokol_dt, lat, lng) VALUES (?, ?, ?, ?, ?, ?,?,?,?)");
//$stmt->bind_param("iisssisdd", $_POST['id'], $_POST['pin'],$p_photo, $p_photo1, $m_photo, $cust_id, $_POST['dt'], $_POST['lat'], $_POST['lng']);
//$stmt->execute();

$result = UpdateOrCreate($_POST['id'], $_POST['pin'],$p_photo, $p_photo1, $m_photo, $cust_id, $_POST['dt'], $_POST['lat'], $_POST['lng']);

if ($result) {
    $output = `cd ../; php7.2 artisan yandex:export $p_photo`;
    $output = `cd ../; php7.2 artisan yandex:export $p_photo1`;
    $output = `cd ../; php7.2 artisan yandex:export $m_photo`;

//echo $output;
    echo "Ok";
}
else {
    echo "error";
}
$stmt->close();
$conn->close();


function UpdateOrCreate($conn, $id, $pin, $p_photo, $p_photo1, $m_photo, $cust_id, $dt, $lat, $lng) {

    $result = false;
    try {
        $stmt = $conn->prepare('select id from protokols where protokol_num=? and pin=?');
        $stmt->bind_param("ii", $id, $pin);
        $stmt->execute();
        $stmt->bind_result($id);
        if (!$stmt->fetch()) {
            $stmt = $conn->prepare("INSERT INTO protokols (protokol_num, pin, protokol_photo, protokol_photo1, meter_photo, customer_id, protokol_dt, lat, lng) VALUES (?, ?, ?, ?, ?, ?,?,?,?)");
            $stmt->bind_param("iisssisdd", $id, $pin, $p_photo, $p_photo1, $m_photo, $cust_id, $dt, $lat, $lng);
            $stmt->execute();
        } else {
            $stmt1 = $conn->prepare("update protokols  set protokol_photo=?, protokol_photo1=?, meter_photo=?, protokol_dt=?, lat=?, lng=?) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt1->bind_param("ssssdd", $p_photo, $p_photo1, $m_photo, $dt, $lat, $lng);
            $stmt1->execute();
        }
        $result = true;
    }
    catch (Exception $e) {
        error_log("Ошибка сохранения данных поверки. ".$e->getMessage(), 0);
        die("Ошибка сохранения данных поверки. ".$e->getMessage());
    }
    return $result;
}

?>
