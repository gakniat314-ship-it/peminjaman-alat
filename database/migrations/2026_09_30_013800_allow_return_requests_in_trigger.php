<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Memperbarui trigger MySQL agar pengembalian yang diajukan peminjam diterima. */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_pengembalian_hitung_denda');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_pengembalian_hitung_denda
BEFORE INSERT ON pengembalian
FOR EACH ROW
BEGIN
    DECLARE v_rencana DATE;
    DECLARE v_status VARCHAR(20);

    SELECT tanggal_kembali_rencana, status
      INTO v_rencana, v_status
      FROM peminjaman
     WHERE id = NEW.peminjaman_id;

    IF v_status NOT IN ('disetujui', 'menunggu_kembali') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Peminjaman belum disetujui atau sudah dikembalikan';
    END IF;

    SET NEW.denda = COALESCE(NEW.denda, 0) + fn_hitung_denda(v_rencana, NEW.tanggal_dikembalikan);
END
SQL);
    }

    /** Memulihkan aturan trigger sebelum pengajuan pengembalian diizinkan. */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_pengembalian_hitung_denda');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_pengembalian_hitung_denda
BEFORE INSERT ON pengembalian
FOR EACH ROW
BEGIN
    DECLARE v_rencana DATE;
    DECLARE v_status VARCHAR(20);

    SELECT tanggal_kembali_rencana, status
      INTO v_rencana, v_status
      FROM peminjaman
     WHERE id = NEW.peminjaman_id;

    IF v_status <> 'disetujui' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Peminjaman belum disetujui atau sudah dikembalikan';
    END IF;

    SET NEW.denda = fn_hitung_denda(v_rencana, NEW.tanggal_dikembalikan);
END
SQL);
    }
};
