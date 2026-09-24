<html>
<head>
    <title>Contoh Penggunaan IF</title>
</head>

<body>

<form method="post">
    Besar Pembelian :
    <input type="text" name="total_beli">
    <br><br>

    <input type="submit" value="Tentukan Diskon">
</form>

<?php
if (isset($_POST['total_beli']))
{
    $total_beli = intval($_POST['total_beli']);

    $diskon = 0;
    $persen = 0;

    if ($total_beli >= 200000)
    {
        $persen = 10;
        $diskon = 0.10;
    }
    else if ($total_beli >= 100000)
    {
        $persen = 5;
        $diskon = 0.05;
    }
    else
    {
        $persen = 1;
        $diskon = 0.01;
    }

    $jumlah_diskon = $diskon * $total_beli;
    $pembayaran = $total_beli - $jumlah_diskon;

    printf("Diskon = %d%% (%d)<br>\n", $persen, $jumlah_diskon);
    printf("Pembayaran = %d<br>\n", $pembayaran);
}
?>

</body>
</html>