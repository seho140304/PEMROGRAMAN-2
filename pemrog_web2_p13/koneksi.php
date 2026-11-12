<?php
// Pengaturan Koneksi Database
$host = "localhost";
$user = "root";
$pass = ""; // Password MySQL Workbench
$db   = "db_kampus";

// Polyfill fungsi mysql_* agar script pada slide tetap berjalan normal di PHP 7 dan PHP 8
if (!function_exists('mysql_connect')) {
    $GLOBALS['mysql_active_link'] = null;

    function mysql_connect($server = 'localhost', $username = 'root', $password = '') {
        // Coba koneksi dengan password yang ditentukan
        $link = @mysqli_connect($server, $username, $password);
        if (!$link && empty($password)) {
            // Coba alternatif password 'rafi123'
            $link = @mysqli_connect($server, $username, 'rafi123');
        }
        $GLOBALS['mysql_active_link'] = $link;
        return $link;
    }

    function mysql_select_db($database_name, $link = null) {
        $conn = $link ? $link : $GLOBALS['mysql_active_link'];
        if (!$conn) return false;
        return @mysqli_select_db($conn, $database_name);
    }

    function mysql_query($query, $link = null) {
        $conn = $link ? $link : $GLOBALS['mysql_active_link'];
        if (!$conn) return false;
        return @mysqli_query($conn, $query);
    }

    function mysql_num_rows($result) {
        if (!$result) return 0;
        return @mysqli_num_rows($result);
    }

    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
        if (!$result) return false;
        return @mysqli_fetch_array($result, $result_type);
    }

    function mysql_close($link = null) {
        $conn = $link ? $link : $GLOBALS['mysql_active_link'];
        if ($conn) {
            return @mysqli_close($conn);
        }
        return true;
    }
}

// Buka koneksi engine MySQL
$Open = mysql_connect($host, $user, $pass);
if (!$Open) {
    die("Koneksi ke Engine MySQL Gagal !<br>");
}

// Buka database
$Koneksi = mysql_select_db($db, $Open);
if (!$Koneksi) {
    die("Koneksi ke Database Gagal !");
}
?>
