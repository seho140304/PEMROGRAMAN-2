
<?php
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";

$link = mysqli_connect(
    $servername,
    $dbusername,
    $dbpassword
);

if (!$link) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

echo "OK....koneksi berhasil";
?>
