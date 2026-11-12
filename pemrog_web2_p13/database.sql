-- Database & Tabel Sesuai Pertemuan 13 (Panjang NIM disesuaikan untuk NIM 12 digit/modern)
CREATE DATABASE IF NOT EXISTS `db_kampus`;
USE `db_kampus`;

CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id_mahasiswa` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `alamat` varchar(150) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  PRIMARY KEY (`id_mahasiswa`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
