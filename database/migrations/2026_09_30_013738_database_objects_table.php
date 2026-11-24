<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Database Objects UKK Peminjaman Alat:
 *  - 1 Function   : fn_hitung_denda
 *  - 4 Trigger    : validasi stok, potong stok, hitung denda otomatis, tambah stok
 *  - 2 Procedure  : sp_setujui_peminjaman, sp_proses_pengembalian (memakai TRANSACTION)
 *
 * Jalankan SETELAH migration 7 tabel (users, kategori, alat, peminjaman,
 * detail_peminjaman, pengembalian, log_aktivitas) selesai dibuat.
 */
return new class extends Migration
{
    /** Membuat function, trigger, dan procedure untuk aturan transaksi pada MySQL. */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Hapus dulu agar migration aman dijalankan ulang
        $this->dropAll();

        // ------------------------------------------------------------------
        // 1) FUNCTION: hitung denda = jumlah hari telat x tarif per hari
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE FUNCTION fn_hitung_denda(p_tgl_rencana DATE, p_tgl_kembali DATE)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    -- Tarif denda per hari (Rp). Ubah di sini jika aturan berubah.
    DECLARE c_tarif DECIMAL(10,2) DEFAULT 5000;
    DECLARE v_telat INT;

    SET v_telat = DATEDIFF(p_tgl_kembali, p_tgl_rencana);

    -- Tepat waktu / lebih awal => tidak ada denda
    IF v_telat > 0 THEN
        RETURN v_telat * c_tarif;
    END IF;

    RETURN 0;
END
SQL);

        // ------------------------------------------------------------------
        // 2) TRIGGER: tolak persetujuan jika stok tidak cukup
        //    BEFORE UPDATE supaya perubahan status bisa dibatalkan
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_peminjaman_validasi_stok
BEFORE UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    IF OLD.status = 'pending' AND NEW.status = 'disetujui' THEN
        IF EXISTS (
            SELECT 1
            FROM detail_peminjaman d
            JOIN alat a ON a.id = d.alat_id
            WHERE d.peminjaman_id = NEW.id
              AND d.jumlah > a.stok
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Stok alat tidak mencukupi';
        END IF;
    END IF;
END
SQL);

        // ------------------------------------------------------------------
        // 3) TRIGGER: potong stok saat peminjaman DISETUJUI
        //    (bukan saat diajukan, karena pengajuan bisa saja ditolak)
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_peminjaman_potong_stok
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    IF OLD.status = 'pending' AND NEW.status = 'disetujui' THEN
        UPDATE alat a
        JOIN detail_peminjaman d ON d.alat_id = a.id
        SET a.stok = a.stok - d.jumlah
        WHERE d.peminjaman_id = NEW.id;
    END IF;
END
SQL);

        // ------------------------------------------------------------------
        // 4) TRIGGER: saat pengembalian dicatat -> validasi status + hitung denda
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_pengembalian_hitung_denda
BEFORE INSERT ON pengembalian
FOR EACH ROW
BEGIN
    DECLARE v_rencana DATE;
    DECLARE v_status  VARCHAR(20);

    SELECT tanggal_kembali_rencana, status
      INTO v_rencana, v_status
      FROM peminjaman
     WHERE id = NEW.peminjaman_id;

    -- Hanya peminjaman yang sudah disetujui yang boleh dikembalikan
    IF v_status NOT IN ('disetujui', 'menunggu_kembali') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Peminjaman belum disetujui atau sudah dikembalikan';
    END IF;

    SET NEW.denda = COALESCE(NEW.denda, 0) + fn_hitung_denda(v_rencana, NEW.tanggal_dikembalikan);
END
SQL);

        // ------------------------------------------------------------------
        // 5) TRIGGER: setelah pengembalian tersimpan -> stok bertambah + status selesai
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_pengembalian_tambah_stok
AFTER INSERT ON pengembalian
FOR EACH ROW
BEGIN
    UPDATE alat a
    JOIN detail_peminjaman d ON d.alat_id = a.id
    SET a.stok = a.stok + d.jumlah
    WHERE d.peminjaman_id = NEW.peminjaman_id
      AND d.kondisi_kembali = 'Baik';

    UPDATE peminjaman
       SET status = 'dikembalikan'
     WHERE id = NEW.peminjaman_id;
END
SQL);

        // ------------------------------------------------------------------
        // 6) STORED PROCEDURE + TRANSACTION: setujui peminjaman
        //    Status berubah + log tercatat, atau dua-duanya batal (ROLLBACK).
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_setujui_peminjaman(IN p_peminjaman_id BIGINT, IN p_petugas_id BIGINT)
BEGIN
    -- Jika ada error apa pun (termasuk stok kurang dari trigger): batalkan semua
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; -- lempar ulang error agar bisa ditangkap Laravel
    END;

    START TRANSACTION;

    UPDATE peminjaman
       SET status = 'disetujui', petugas_id = p_petugas_id
     WHERE id = p_peminjaman_id AND status = 'pending';

    -- Tidak ada baris berubah = ID salah atau status bukan pending
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Peminjaman tidak ditemukan atau bukan berstatus pending';
    END IF;

    INSERT INTO log_aktivitas (user_id, aktivitas, created_at)
    VALUES (p_petugas_id, CONCAT('Menyetujui peminjaman #', p_peminjaman_id), NOW());

    COMMIT;
END
SQL);

        // ------------------------------------------------------------------
        // 7) STORED PROCEDURE + TRANSACTION: proses pengembalian
        //    Denda dan stok ditangani trigger; procedure menjaga semuanya atomik.
        // ------------------------------------------------------------------
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_proses_pengembalian(
    IN p_peminjaman_id BIGINT,
    IN p_petugas_id BIGINT,
    IN p_tanggal_dikembalikan DATE
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- kolom denda diisi otomatis oleh trg_pengembalian_hitung_denda
    INSERT INTO pengembalian (peminjaman_id, tanggal_dikembalikan, petugas_id)
    VALUES (p_peminjaman_id, p_tanggal_dikembalikan, p_petugas_id);

    INSERT INTO log_aktivitas (user_id, aktivitas, created_at)
    VALUES (p_petugas_id, CONCAT('Memproses pengembalian peminjaman #', p_peminjaman_id), NOW());

    COMMIT;
END
SQL);
    }

    /** Menghapus seluruh objek basis data yang dibuat oleh migrasi ini. */
    public function down(): void
    {
        $this->dropAll();
    }

    /** Hapus semua database object buatan migration ini. */
    private function dropAll(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_proses_pengembalian');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_setujui_peminjaman');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_pengembalian_tambah_stok');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_pengembalian_hitung_denda');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_peminjaman_potong_stok');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_peminjaman_validasi_stok');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_hitung_denda');
    }
};
