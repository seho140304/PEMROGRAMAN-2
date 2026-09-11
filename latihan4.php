<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan 4</title>
</head>
<body>

<?php

$nama_hari = date("l");

if ($nama_hari == "Monday") {
    echo "<h1>Hari ini adalah hari Senin</h1>";
    echo "Meeting awal minggu";

} elseif ($nama_hari == "Tuesday") {
    echo "<h1>Hari ini adalah hari Selasa</h1>";

} elseif ($nama_hari == "Wednesday") {
    echo "<h1>Hari ini adalah hari Rabu</h1>";

} elseif ($nama_hari == "Thursday") {
    echo "<h1>Hari ini adalah hari Kamis</h1>";

} elseif ($nama_hari == "Friday") {
    echo "<h1>Hari ini adalah hari Jumat</h1>";
    echo "Salat Jum'at bersama";

} elseif ($nama_hari == "Saturday") {
    echo "<h1>Hari ini adalah hari Sabtu</h1>";

} elseif ($nama_hari == "Sunday") {
    echo "<h1>Hari ini adalah hari Minggu</h1>";
    echo "Waktu istirahat";

}

?>

</body>
</html>