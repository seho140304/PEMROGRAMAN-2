<?php
session_start();

// Konfigurasi database XAMPP. Sesuaikan bila username/password MySQL berbeda.
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'db_calon_mhs';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = null;
$connectionError = '';
$flashMessage = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $connectionError = 'Koneksi database gagal. Pastikan MySQL aktif dan file calon_mhs.sql sudah diimpor melalui phpMyAdmin.';
}

/** Mengubah nilai menjadi teks yang aman ditampilkan di HTML. */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Menguraikan kode contoh seperti A2-101-9. */
function uraikanKodePendaftaran(string $kodePendaftaran): ?array
{
    if (!preg_match('/^([ABV])([1-9])-(\d{3})-([0-9OND])$/i', $kodePendaftaran, $m)) {
        return null;
    }

    $lokasi = [
        'A' => 'Gedung A',
        'B' => 'Gedung B',
        'V' => 'Viktor',
    ];
    // Kode bulan satu karakter: 1-9 untuk Januari-September, 0 untuk Oktober,
    // N untuk November, dan D untuk Desember.
    $bulan = [
        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret',
        '4' => 'April', '5' => 'Mei', '6' => 'Juni',
        '7' => 'Juli', '8' => 'Agustus', '9' => 'September',
        '0' => 'Oktober', 'N' => 'November', 'D' => 'Desember',
    ];

    $tempat = strtoupper($m[1]);
    $gelombang = (int)$m[2];
    $kodeBulan = strtoupper($m[4]);

    return [
        'kode' => $tempat . $gelombang . ' - ' . $lokasi[$tempat] . ' - Gelombang ' . $gelombang . ' - ' . $bulan[$kodeBulan],
        'lokasi' => $lokasi[$tempat],
        'gelombang' => $gelombang,
        'bulan' => $bulan[$kodeBulan],
    ];
}

// Proses input dan penyimpanan data calon mahasiswa ke database.
if ($conn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $kodePendaftaran = strtoupper(trim($_POST['kode_pendaftaran'] ?? ''));
    $namaPendaftar = trim($_POST['nama_pendaftar'] ?? '');
    $jenisKelamin = trim($_POST['jenis_kelamin'] ?? '');
    $ttl = trim($_POST['ttl'] ?? '');
    $asalSekolah = trim($_POST['asal_sekolah'] ?? '');
    $pekerjaanOrtu = trim($_POST['pekerjaan_ortu'] ?? '');
    $nilaiMatematika = filter_var($_POST['nilai_matematika'] ?? null, FILTER_VALIDATE_INT);
    $nilaiBahasaInggris = filter_var($_POST['nilai_bahasa_inggris'] ?? null, FILTER_VALIDATE_INT);
    $nilaiUmum = filter_var($_POST['nilai_umum'] ?? null, FILTER_VALIDATE_INT);

    $hasilKode = uraikanKodePendaftaran($kodePendaftaran);

    if ($kodePendaftaran === '' || $namaPendaftar === '' || $jenisKelamin === '' ||
        $ttl === '' || $asalSekolah === '' || $pekerjaanOrtu === '') {
        $_SESSION['flash_message'] = 'Semua kolom wajib diisi terlebih dahulu.';
        $_SESSION['flash_type'] = 'error';
    } elseif (!$hasilKode) {
        $_SESSION['flash_message'] = 'Format kode pendaftaran salah. Gunakan contoh A2-101-9 (A/B/V, nomor gelombang, 3 angka nomor peserta, dan kode bulan).';
        $_SESSION['flash_type'] = 'error';
    } elseif (!in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)) {
        $_SESSION['flash_message'] = 'Silakan pilih jenis kelamin yang tersedia.';
        $_SESSION['flash_type'] = 'error';
    } elseif ($nilaiMatematika === false || $nilaiBahasaInggris === false || $nilaiUmum === false ||
        $nilaiMatematika < 0 || $nilaiMatematika > 100 ||
        $nilaiBahasaInggris < 0 || $nilaiBahasaInggris > 100 ||
        $nilaiUmum < 0 || $nilaiUmum > 100) {
        $_SESSION['flash_message'] = 'Nilai Matematika, Bahasa Inggris, dan Umum harus berupa angka 0 sampai 100.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $kode = $hasilKode['kode'];
        $rataRataAsli = ($nilaiMatematika + $nilaiBahasaInggris + $nilaiUmum) / 3;
        $rataRata = round($rataRataAsli, 2);

        if ($rataRataAsli >= 70) {
            $keterangan = 'Lulus';
        } elseif ($rataRataAsli >= 60) {
            $keterangan = 'Cadangan';
        } else {
            $keterangan = 'Tidak Lulus';
        }

        try {
            $stmt = $conn->prepare(
                'INSERT INTO calon_mhs
                (kode, kode_pendaftaran, nama_pendaftar, jenis_kelamin, ttl, asal_sekolah, pekerjaan_ortu,
                 nilai_matematika, nilai_bahasa_inggris, nilai_umum, rata_rata, keterangan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'sssssssiiids',
                $kode,
                $kodePendaftaran,
                $namaPendaftar,
                $jenisKelamin,
                $ttl,
                $asalSekolah,
                $pekerjaanOrtu,
                $nilaiMatematika,
                $nilaiBahasaInggris,
                $nilaiUmum,
                $rataRata,
                $keterangan
            );
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'Data calon mahasiswa berhasil disimpan ke database.';
            $_SESSION['flash_type'] = 'success';
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                $_SESSION['flash_message'] = 'Kode pendaftaran tersebut sudah digunakan. Gunakan kode yang berbeda.';
            } else {
                $_SESSION['flash_message'] = 'Data gagal disimpan. Pastikan tabel calon_mhs sudah dibuat dari file calon_mhs.sql.';
            }
            $_SESSION['flash_type'] = 'error';
        }
    }

    // Redirect mencegah data tersimpan dua kali ketika halaman di-refresh.
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Mengambil data yang tersimpan untuk ditampilkan pada tabel HTML.
$dataCalon = [];
$jumlahPendaftar = 0;
$jumlahLulus = 0;
$jumlahCadangan = 0;
$jumlahTidakLulus = 0;

if ($conn) {
    try {
        $result = $conn->query('SELECT * FROM calon_mhs ORDER BY id ASC');
        while ($row = $result->fetch_assoc()) {
            $dataCalon[] = $row;
            if ($row['keterangan'] === 'Lulus') {
                $jumlahLulus++;
            } elseif ($row['keterangan'] === 'Cadangan') {
                $jumlahCadangan++;
            } elseif ($row['keterangan'] === 'Tidak Lulus') {
                $jumlahTidakLulus++;
            }
        }
        $result->free();
        $jumlahPendaftar = count($dataCalon);
    } catch (mysqli_sql_exception $e) {
        $connectionError = 'Tabel calon_mhs belum tersedia. Import file calon_mhs.sql terlebih dahulu.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Pendaftaran Calon Mahasiswa</title>
    <style>
        :root {
            --navy: #172554;
            --blue: #1d4ed8;
            --blue-dark: #1e40af;
            --bg: #f4f7fb;
            --text: #172033;
            --muted: #64748b;
            --line: #dbe3ee;
            --white: #fff;
        }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--text); background: var(--bg); font-family: Arial, Helvetica, sans-serif; }
        .hero { background: linear-gradient(135deg, #172554, #1d4ed8); color: #fff; padding: 28px 20px; }
        .hero-inner { max-width: 1320px; margin: auto; }
        .eyebrow { margin: 0 0 8px; font-size: 12px; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; color: #bfdbfe; }
        h1 { margin: 0 0 8px; font-size: clamp(24px, 3vw, 34px); }
        .hero p { margin: 0; color: #dbeafe; line-height: 1.55; }
        .container { width: min(1320px, calc(100% - 32px)); margin: 24px auto 44px; }
        .panel { margin-bottom: 22px; padding: 24px; background: var(--white); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 6px 22px rgba(15, 23, 42, .045); }
        .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; }
        h2 { margin: 0; font-size: 20px; color: var(--navy); }
        .count-pill { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: #eff6ff; color: #1d4ed8; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 17px; }
        .field { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
        .field label { font-size: 13px; font-weight: 700; }
        .required { color: #dc2626; }
        input, select { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: var(--text); font: inherit; font-size: 14px; }
        input:focus, select:focus { outline: 3px solid rgba(37, 99, 235, .14); border-color: #3b82f6; }
        input[type=number] { appearance: textfield; }
        .section-label { grid-column: 1 / -1; margin: 4px 0 -3px; padding-bottom: 9px; border-bottom: 1px solid #e8edf5; color: #334155; font-size: 13px; font-weight: 800; }
        .hint { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.5; }
        .code-preview { padding: 10px 12px; border: 1px dashed #93c5fd; border-radius: 8px; background: #eff6ff; color: #1e40af; font-size: 13px; line-height: 1.5; min-height: 40px; }
        .actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
        button { border: 0; border-radius: 8px; padding: 11px 20px; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; }
        .btn-primary { color: #fff; background: var(--blue); }
        .btn-primary:hover { background: var(--blue-dark); }
        .btn-secondary { color: #334155; background: #e2e8f0; }
        .alert { margin-bottom: 20px; padding: 13px 16px; border-radius: 9px; font-size: 14px; line-height: 1.5; }
        .alert.success { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .alert.error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
        .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px; }
        .stat { padding: 15px 16px; border: 1px solid var(--line); border-radius: 11px; background: #fbfdff; }
        .stat span { display: block; color: var(--muted); font-size: 12px; margin-bottom: 8px; }
        .stat strong { font-size: 25px; color: var(--navy); }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 9px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; white-space: nowrap; }
        th, td { padding: 11px 10px; border-bottom: 1px solid #e5eaf1; text-align: left; vertical-align: middle; }
        th { background: #eff4fb; color: #334155; font-size: 11px; text-transform: uppercase; letter-spacing: .025em; }
        tbody tr:nth-child(even) { background: #fbfdff; }
        tbody tr:hover { background: #f1f7ff; }
        tfoot th, tfoot td { background: #f8fafc; font-weight: 700; }
        .empty { padding: 28px 16px; color: var(--muted); text-align: center; }
        .status { display: inline-block; padding: 5px 8px; border-radius: 999px; font-size: 11px; font-weight: 800; }
        .status.lulus { color: #166534; background: #dcfce7; }
        .status.cadangan { color: #92400e; background: #fef3c7; }
        .status.tidak-lulus { color: #991b1b; background: #fee2e2; }
        .notes { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 18px; }
        .note { padding: 15px 17px; background: #f8fafc; border: 1px solid var(--line); border-radius: 10px; font-size: 13px; line-height: 1.65; }
        .note strong { color: var(--navy); }
        .note ul { margin: 8px 0 0; padding-left: 18px; }
        footer { text-align: center; color: #94a3b8; font-size: 12px; padding: 8px 0 0; }
        @media (max-width: 850px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) { .container { width: calc(100% - 20px); margin-top: 14px; } .panel { padding: 16px; } .grid, .notes { grid-template-columns: 1fr; } .section-label { grid-column: auto; } .actions { flex-direction: column-reverse; } .actions button { width: 100%; } }
    </style>
</head>
<body>
<header class="hero">
    <div class="hero-inner">
        <p class="eyebrow">Pemrograman Web II · Universitas Pamulang</p>
        <h1>Pendaftaran Calon Mahasiswa Baru</h1>
        <p>Form input, proses penyimpanan PHP ke MySQL, dan laporan data calon mahasiswa.</p>
    </div>
</header>

<main class="container">
    <?php if ($connectionError !== ''): ?>
        <div class="alert error"><?= e($connectionError) ?></div>
    <?php endif; ?>
    <?php if ($flashMessage !== ''): ?>
        <div class="alert <?= $flashType === 'error' ? 'error' : 'success' ?>"><?= e($flashMessage) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="panel-heading">
            <h2>Input Pendaftaran</h2>
            <span class="count-pill">Jumlah data: <?= (int)$jumlahPendaftar ?></span>
        </div>
        <form method="post" action="" id="formPendaftaran">
            <div class="grid">
                <div class="section-label">Identitas pendaftar</div>
                <div class="field">
                    <label for="kode_pendaftaran">Kode Pendaftaran <span class="required">*</span></label>
                    <input type="text" id="kode_pendaftaran" name="kode_pendaftaran" maxlength="10" placeholder="Contoh: A2-101-9" pattern="[ABVabv][1-9]-[0-9]{3}-[0-9ONDond]" title="Contoh format yang benar: A2-101-9" required>
                    <p class="hint">Format: kode tempat dan gelombang - nomor peserta - kode bulan.</p>
                </div>
                <div class="field">
                    <label for="nama_pendaftar">Nama Pendaftar <span class="required">*</span></label>
                    <input type="text" id="nama_pendaftar" name="nama_pendaftar" maxlength="100" placeholder="Nama lengkap" required>
                </div>
                <div class="field">
                    <label for="jenis_kelamin">Jenis Kelamin <span class="required">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">-- Pilih jenis kelamin --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="field">
                    <label for="ttl">TTL <span class="required">*</span></label>
                    <input type="text" id="ttl" name="ttl" maxlength="100" placeholder="Tempat, tanggal lahir" required>
                </div>
                <div class="field">
                    <label for="asal_sekolah">Asal Sekolah <span class="required">*</span></label>
                    <input type="text" id="asal_sekolah" name="asal_sekolah" maxlength="120" placeholder="Nama sekolah asal" required>
                </div>
                <div class="field">
                    <label for="pekerjaan_ortu">Pekerjaan Orang Tua <span class="required">*</span></label>
                    <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" maxlength="100" placeholder="Pekerjaan orang tua" required>
                </div>
                <div class="field">
                    <label>Hasil pembacaan kode</label>
                    <div class="code-preview" id="kodePreview">Detail tempat tes, gelombang, dan bulan muncul di sini.</div>
                </div>

                <div class="section-label">Nilai tes (0–100)</div>
                <div class="field">
                    <label for="nilai_matematika">Matematika <span class="required">*</span></label>
                    <input type="number" id="nilai_matematika" name="nilai_matematika" min="0" max="100" step="1" placeholder="0 - 100" required>
                </div>
                <div class="field">
                    <label for="nilai_bahasa_inggris">Bahasa Inggris <span class="required">*</span></label>
                    <input type="number" id="nilai_bahasa_inggris" name="nilai_bahasa_inggris" min="0" max="100" step="1" placeholder="0 - 100" required>
                </div>
                <div class="field">
                    <label for="nilai_umum">Umum <span class="required">*</span></label>
                    <input type="number" id="nilai_umum" name="nilai_umum" min="0" max="100" step="1" placeholder="0 - 100" required>
                </div>
                <div class="field">
                    <label>Pratinjau rata-rata</label>
                    <div class="code-preview" id="rataPreview">Rata-rata akan dihitung otomatis.</div>
                </div>
            </div>
            <div class="actions">
                <button class="btn-secondary" type="reset">Batal</button>
                <button class="btn-primary" type="submit" name="simpan" value="1">Simpan Data</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Tabel Data Calon Mahasiswa</h2>
            <span class="count-pill"><?= (int)$jumlahPendaftar ?> pendaftar tersimpan</span>
        </div>

        <div class="summary">
            <div class="stat"><span>Jumlah Pendaftar</span><strong><?= (int)$jumlahPendaftar ?></strong></div>
            <div class="stat"><span>Peserta Lulus</span><strong><?= (int)$jumlahLulus ?></strong></div>
            <div class="stat"><span>Peserta Cadangan</span><strong><?= (int)$jumlahCadangan ?></strong></div>
            <div class="stat"><span>Tidak Lulus</span><strong><?= (int)$jumlahTidakLulus ?></strong></div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Kode Pendf.</th>
                        <th>Nama Pendft.</th>
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
                <?php if (count($dataCalon) === 0): ?>
                    <tr><td class="empty" colspan="13">Belum ada data. Silakan isi formulir dan klik Simpan Data.</td></tr>
                <?php else: ?>
                    <?php foreach ($dataCalon as $i => $row): ?>
                        <?php $statusClass = $row['keterangan'] === 'Lulus' ? 'lulus' : ($row['keterangan'] === 'Cadangan' ? 'cadangan' : 'tidak-lulus'); ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($row['kode']) ?></td>
                            <td><strong><?= e($row['kode_pendaftaran']) ?></strong></td>
                            <td><?= e($row['nama_pendaftar']) ?></td>
                            <td><?= e($row['jenis_kelamin']) ?></td>
                            <td><?= e($row['ttl']) ?></td>
                            <td><?= e($row['asal_sekolah']) ?></td>
                            <td><?= e($row['pekerjaan_ortu']) ?></td>
                            <td><?= (int)$row['nilai_matematika'] ?></td>
                            <td><?= (int)$row['nilai_bahasa_inggris'] ?></td>
                            <td><?= (int)$row['nilai_umum'] ?></td>
                            <td><?= number_format((float)$row['rata_rata'], 2, ',', '.') ?></td>
                            <td><span class="status <?= e($statusClass) ?>"><?= e($row['keterangan']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="11">JUMLAH PENDAFTAR</td><td colspan="2"><?= (int)$jumlahPendaftar ?></td></tr>
                    <tr><td colspan="11">JUMLAH PESERTA LULUS</td><td colspan="2"><?= (int)$jumlahLulus ?></td></tr>
                    <tr><td colspan="11">JUMLAH TIDAK PESERTA LULUS</td><td colspan="2"><?= (int)$jumlahTidakLulus ?></td></tr>
                </tfoot>
            </table>
        </div>

        <div class="notes">
            <div class="note">
                <strong>Keterangan kode pendaftaran</strong>
                <ul>
                    <li>Karakter awal: A = Gedung A, B = Gedung B, V = Viktor.</li>
                    <li>Karakter kedua menunjukkan gelombang tes.</li>
                    <li>Contoh <strong>A2-101-9</strong>: Gedung A, Gelombang 2, nomor peserta 101, bulan September.</li>
                    <li>Kode bulan satu karakter: 1–9 = Januari–September, 0 = Oktober, N = November, D = Desember.</li>
                </ul>
            </div>
            <div class="note">
                <strong>Ketentuan hasil tes</strong>
                <ul>
                    <li>Rata-rata = (Matematika + Bahasa Inggris + Umum) ÷ 3.</li>
                    <li>Rata-rata 70 atau lebih: <strong>Lulus</strong>.</li>
                    <li>Rata-rata 60 sampai kurang dari 70: <strong>Cadangan</strong>.</li>
                    <li>Rata-rata kurang dari 60: <strong>Tidak Lulus</strong>.</li>
                </ul>
            </div>
        </div>
    </section>
    <footer>Tugas UTS · Pemrograman Web II · Data tersimpan pada database MySQL</footer>
</main>

<script>
const inputKode = document.getElementById('kode_pendaftaran');
const kodePreview = document.getElementById('kodePreview');
const skorInputs = [
    document.getElementById('nilai_matematika'),
    document.getElementById('nilai_bahasa_inggris'),
    document.getElementById('nilai_umum')
];
const rataPreview = document.getElementById('rataPreview');

function updateKodePreview() {
    const kode = inputKode.value.trim().toUpperCase();
    const match = kode.match(/^([ABV])([1-9])-(\d{3})-([0-9OND])$/);
    if (!match) {
        kodePreview.textContent = 'Contoh format: A2-101-9';
        return;
    }
    const tempat = { A: 'Gedung A', B: 'Gedung B', V: 'Viktor' }[match[1]];
    const bulan = { '1': 'Januari', '2': 'Februari', '3': 'Maret', '4': 'April', '5': 'Mei', '6': 'Juni', '7': 'Juli', '8': 'Agustus', '9': 'September', '0': 'Oktober', N: 'November', D: 'Desember' }[match[4]];
    kodePreview.textContent = `${tempat} · Gelombang ${match[2]} · Nomor ${match[3]} · ${bulan}`;
}

function updateRataPreview() {
    const values = skorInputs.map(input => input.value.trim());
    if (values.some(value => value === '')) {
        rataPreview.textContent = 'Isi ketiga nilai untuk melihat rata-rata.';
        return;
    }
    const nums = values.map(Number);
    if (nums.some(value => !Number.isFinite(value) || value < 0 || value > 100)) {
        rataPreview.textContent = 'Nilai harus berada di antara 0 dan 100.';
        return;
    }
    const rata = nums.reduce((total, value) => total + value, 0) / nums.length;
    const hasil = rata >= 70 ? 'Lulus' : (rata >= 60 ? 'Cadangan' : 'Tidak Lulus');
    rataPreview.textContent = `Rata-rata: ${rata.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} · ${hasil}`;
}

inputKode.addEventListener('input', updateKodePreview);
skorInputs.forEach(input => input.addEventListener('input', updateRataPreview));
document.getElementById('formPendaftaran').addEventListener('reset', () => {
    setTimeout(() => {
        updateKodePreview();
        updateRataPreview();
    }, 0);
});
</script>
</body>
</html>
