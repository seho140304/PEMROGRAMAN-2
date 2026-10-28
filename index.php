<?php
// Koneksi Database (Sesuaikan hostname, username, password, dan nama database)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_pendaftaran";

$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Proses Simpan Data jika tombol SUBMIT ditekan
$pesan = "";
if (isset($_POST['submit'])) {
    $kode_pendaftaran = mysqli_real_escape_string($koneksi, $_POST['kode_pendaftaran']);
    $nama_pendaftar   = mysqli_real_escape_string($koneksi, $_POST['nama_pendaftar']);
    $jenis_kelamin    = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $ttl              = mysqli_real_escape_string($koneksi, $_POST['ttl']);
    $asal_sekolah     = mysqli_real_escape_string($koneksi, $_POST['asal_sekolah']);
    $pekerjaan_ortu   = mysqli_real_escape_string($koneksi, $_POST['pekerjaan_ortu']);
    $nilai_mat        = floatval($_POST['nilai_matematika']);
    $nilai_ing        = floatval($_POST['nilai_b_inggris']);
    $nilai_um         = floatval($_POST['nilai_umum']);

    // Perhitungan Rata-rata
    $rata_rata = ($nilai_mat + $nilai_ing + $nilai_um) / 3;

    // Penentuan Keterangan Sesuai Ketentuan Soal UTS
    if ($rata_rata >= 70) {
        $keterangan = "Lulus";
    } elseif ($rata_rata >= 60 && $rata_rata < 70) {
        $keterangan = "Cadangan";
    } else {
        $keterangan = "Tidak Lulus";
    }

    // Query Insert ke tabel calon_mhs
    $query = "INSERT INTO calon_mhs (kode_pendaftaran, nama_pendaftar, jenis_kelamin, ttl, asal_sekolah, pekerjaan_ortu, nilai_matematika, nilai_b_inggris, nilai_umum, rata_rata, keterangan) 
              VALUES ('$kode_pendaftaran', '$nama_pendaftar', '$jenis_kelamin', '$ttl', '$asal_sekolah', '$pekerjaan_ortu', '$nilai_mat', '$nilai_ing', '$nilai_um', '$rata_rata', '$keterangan')";

    if (mysqli_query($koneksi, $query)) {
        $pesan = "Data pendaftaran berhasil disimpan!";
    } else {
        $pesan = "Error: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran Mahasiswa Baru - Universitas Pamulang</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 14px; color: #333; }
        h2, h3 { text-align: center; }
        .header-title { text-align: center; margin-bottom: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table.form-table { width: 100%; margin-bottom: 15px; }
        table.form-table td { padding: 5px; }
        input[type="text"], select { width: 100%; padding: 5px; box-sizing: border-box; }
        .btn-group { text-align: center; margin-top: 10px; }
        input[type="submit"], input[type="reset"] { padding: 6px 15px; font-weight: bold; cursor: pointer; }
        .alert { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; text-align: center; }
        
        /* Tabel Data Output */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        table.data-table th, table.data-table td { border: 1px solid #333; padding: 6px; text-align: center; }
        table.data-table th { background-color: #f2f2f2; }
        .summary-box { margin-top: 15px; width: 350px; float: right; border-collapse: collapse; font-size: 12px; }
        .summary-box td { border: 1px solid #333; padding: 5px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-title">
        <h3>YAYASAN SASMITA JAYA</h3>
        <h2>UNIVERSITAS PAMULANG</h2>
        <h4>FAKULTAS ILMU KOMPUTER - TEKNIK INFORMATIKA S-1</h4>
        <p><i>Formulir Pendaftaran Mahasiswa Baru (Berbasis Database)</i></p>
    </div>

    <?php if (!empty($pesan)) : ?>
        <div class="alert"><?= $pesan; ?></div>
    <?php endif; ?>

    <!-- FORM INPUT (Bobot Penilaian: 25) -->
    <form action="" method="POST">
        <table class="form-table">
            <tr>
                <td width="25%">KODE PENDAFTARAN</td>
                <td width="2%()">:</td>
                <td><input type="text" name="kode_pendaftaran" placeholder="Contoh: A2-101-9" required></td>
            </tr>
            <tr>
                <td>NAMA PENDAFTAR</td>
                <td>:</td>
                <td><input type="text" name="nama_pendaftar" required></td>
            </tr>
            <tr>
                <td>JENIS KELAMIN</td>
                <td>:</td>
                <td>
                    <input type="radio" name="jenis_kelamin" value="Laki-laki" required> Laki-laki &nbsp;&nbsp;
                    <input type="radio" name="jenis_kelamin" value="Perempuan" required> Perempuan
                </td>
            </tr>
            <tr>
                <td>TTL (Tempat, Tgl Lahir)</td>
                <td>:</td>
                <td><input type="text" name="ttl" placeholder="Contoh: Jakarta, 12 Januari 2004" required></td>
            </tr>
            <tr>
                <td>ASAL SEKOLAH</td>
                <td>:</td>
                <td><input type="text" name="asal_sekolah" required></td>
            </tr>
            <tr>
                <td>PEKERJAAN ORTU</td>
                <td>:</td>
                <td><input type="text" name="pekerjaan_ortu" required></td>
            </tr>
            <tr>
                <td colspan="3"><strong>NILAI TES:</strong></td>
            </tr>
            <tr>
                <td>- Matematika</td>
                <td>:</td>
                <td><input type="text" name="nilai_matematika" placeholder="0 - 100" required></td>
            </tr>
            <tr>
                <td>- Bahasa Inggris</td>
                <td>:</td>
                <td><input type="text" name="nilai_b_inggris" placeholder="0 - 100" required></td>
            </tr>
            <tr>
                <td>- Umum</td>
                <td>:</td>
                <td><input type="text" name="nilai_umum" placeholder="0 - 100" required></td>
            </tr>
        </table>

        <div class="btn-group">
            <input type="submit" name="submit" value="SUBMIT">
            <input type="reset" value="BATAL">
        </div>
    </form>

    <hr style="margin: 30px 0;">

    <!-- TABEL OUTPUT DATA (Bobot Penilaian: 25) -->
    <h3>TABEL DATA CALON MAHASISWA</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode</th>
                <th>Nama Pendf.</th>
                <th>Jns Kel</th>
                <th>TTL</th>
                <th>Asal Sekolah</th>
                <th>Pek. Ortu</th>
                <th>Mat</th>
                <th>B. Ingr</th>
                <th>Umum</th>
                <th>Rata-Rata</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_tampil = "SELECT * FROM calon_mhs ORDER BY id DESC";
            $result = mysqli_query($koneksi, $sql_tampil);
            
            $no = 1;
            $jml_pendaftar = 0;
            $jml_lulus = 0;
            $jml_tidak_lulus = 0;

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $jml_pendaftar++;
                    if ($row['keterangan'] == 'Lulus') {
                        $jml_lulus++;
                    } else {
                        $jml_tidak_lulus++;
                    }
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['kode_pendaftaran']); ?></td>
                <td><?= htmlspecialchars($row['nama_pendaftar']); ?></td>
                <td><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
                <td><?= htmlspecialchars($row['ttl']); ?></td>
                <td><?= htmlspecialchars($row['asal_sekolah']); ?></td>
                <td><?= htmlspecialchars($row['pekerjaan_ortu']); ?></td>
                <td><?= $row['nilai_matematika']; ?></td>
                <td><?= $row['nilai_b_inggris']; ?></td>
                <td><?= $row['nilai_umum']; ?></td>
                <td><?= number_format($row['rata_rata'], 2); ?></td>
                <td><strong><?= $row['keterangan']; ?></strong></td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='12' style='text-align:center;'>Belum ada data pendaftaran.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- RINGKASAN REKAPITULASI -->
    <div style="overflow: hidden; margin-top: 10px;">
        <table class="summary-box" align="right">
            <tr>
                <td>JUMLAH PENDAFTAR</td>
                <td align="center"><strong><?= $jml_pendaftar; ?></strong></td>
            </tr>
            <tr>
                <td>JUMLAH PESERTA LULUS</td>
                <td align="center"><strong><?= $jml_lulus; ?></strong></td>
            </tr>
            <tr>
                <td>JUMLAH TIDAK LULUS / CADANGAN</td>
                <td align="center"><strong><?= $jml_tidak_lulus; ?></strong></td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>