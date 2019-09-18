<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>Свидетельство о поверке</title>
    <link rel="stylesheet" href="css/style.css">
    <SCRIPT type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></SCRIPT>
    <script type="text/javascript" src="js/popup_img.js"></script>
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

?>

<table class="protokol_tbl">
    <tr>
        <td colspan="2" style="font-size: 22px;padding: 20px 0;font-weight: bold;"><b>Свидетельство: № </b> <?=$protokol_formated_num?>  <b>от</b> <?=date("d.m.Y", strtotime($protokol_dt));?></td>
    </tr>
    <tr>
        <td>Свидетельство сторона 1</td>
        <td>Свидетельство сторона 2</td>
        <td>Счетчик</td>
    </tr>
    <tr>
        <td><img class="image" src="/preview/<?$matches[1]?>/<?$matches[2]?>/<?=$protokol_photo?>"></img></td>
        <td><img class="image" src="/preview/<?$matches[1]?>/<?$matches[2]?>/<?=$protokol_photo1?>"></img></td>
        <td><img class="image"  src="/preview/<?$matches[1]?>/<?$matches[2]?>/<?=$meter_photo?>"></td>
    </tr>
</table>

</body>

</html>
