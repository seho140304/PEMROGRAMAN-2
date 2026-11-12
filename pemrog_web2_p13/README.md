# Latihan Pertemuan 13 - Pemrograman Web 2 (Universitas Pamulang)
## Bab VIII: Manipulasi Database Lanjutan

### Cara Menjalankan Program:
#### Cara 1 (Paling Mudah):
Cukup **klik ganda (double-click)** file [`run.bat`](file:///d:/Pemrograman_web2/Pertemuan%2013/pemrog_web2_p13/run.bat).
File ini otomatis menyalakan server PHP dan langsung membuka halaman form di browser Anda!

#### Cara 2 (Lewat Terminal / PowerShell):
1. Buka Terminal di VS Code / folder ini.
2. Jalankan perintah:
   ```powershell
   & "D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -S localhost:8001
   ```
3. Buka browser ke:
   `http://localhost:8001/download-script-form-input-data.php`

---

### Daftar File Program:
1. [`download-script-form-input-data.php`](file:///d:/Pemrograman_web2/Pertemuan%2013/pemrog_web2_p13/download-script-form-input-data.php) - Form input data mahasiswa (HTML & CSS).
2. [`action-input-data.php`](file:///d:/Pemrograman_web2/Pertemuan%2013/pemrog_web2_p13/action-input-data.php) - Script pemrosesan form, validasi kosong & duplikasi NIM, dan INSERT ke database.
3. [`koneksi.php`](file:///d:/Pemrograman_web2/Pertemuan%2013/pemrog_web2_p13/koneksi.php) - Konfigurasi koneksi MySQL dengan password `rafi123`.
4. [`database.sql`](file:///d:/Pemrograman_web2/Pertemuan%2013/pemrog_web2_p13/database.sql) - Skema database `db_kampus` dan tabel `mahasiswa`.
5. [`run.bat`](file:///d:/Pemrograman_web2/Pertemuan%2013/pemrog_web2_p13/run.bat) - Script jalan cepat 1-klik.
