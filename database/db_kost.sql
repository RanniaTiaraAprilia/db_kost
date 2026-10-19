-- DATABASE SISTEM INFORMASI KOST
-- Tiga tabel: pengguna, penyewa, dan sewa.
-- Rekap pembayaran dihitung otomatis dari tabel sewa.

CREATE DATABASE IF NOT EXISTS db_kost
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_kost;

CREATE TABLE IF NOT EXISTS pengguna (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS penyewa (
  id_penyewa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nik VARCHAR(20) NOT NULL UNIQUE,
  no_hp VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sewa (
  id_sewa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_penyewa INT UNSIGNED NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  total_biaya BIGINT UNSIGNED NOT NULL DEFAULT 0,
  jumlah_bayar BIGINT UNSIGNED NOT NULL DEFAULT 0,
  status_bayar ENUM('Lunas','Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
  catatan TEXT NULL,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sewa_penyewa FOREIGN KEY (id_penyewa)
    REFERENCES penyewa(id_penyewa)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_status_bayar (status_bayar),
  INDEX idx_periode (tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB;

-- Login demo project: username admin, password admin123.
-- Login demo diproses oleh login.php. Untuk akun database tambahan,
-- simpan password menggunakan password_hash() dari PHP.
