<?php

use App\Support\Pii;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mã hóa PII ngay trên các bảng hiện có (KHACH_HANG, NHAN_VIEN, ORDERS, NHA_CUNG_CAP):
 *   - Đổi cột PII sang TEXT để chứa ciphertext (AES-256-GCM của Laravel Crypt).
 *   - Thêm KHACH_HANG.sdt_hash (blind index) để tra cứu/gộp theo SĐT.
 *   - Mã hóa dữ liệu cũ + điền sdt_hash (idempotent).
 *
 * Đọc/ghi các cột này phải qua Eloquent cast 'encrypted' (model) hoặc Pii::tryDecrypt
 * (với truy vấn Query Builder thô).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Mở rộng kiểu cột → TEXT (đủ chứa ciphertext).
        Schema::table('KHACH_HANG', function (Blueprint $table) {
            $table->text('ten_kh')->change();
            $table->text('sdt')->nullable()->change();
            $table->char('sdt_hash', 64)->nullable()->after('sdt');
            $table->index('sdt_hash');
        });
        Schema::table('NHAN_VIEN', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->text('sdt')->nullable()->change();
            $table->text('cccd')->nullable()->change();
            $table->text('dia_chi')->nullable()->change();
        });
        Schema::table('ORDERS', function (Blueprint $table) {
            $table->text('ten_khach')->nullable()->change();
            $table->text('sdt_khach')->nullable()->change();
        });
        Schema::table('NHA_CUNG_CAP', function (Blueprint $table) {
            $table->text('sdt')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('dia_chi')->nullable()->change();
        });

        // 2) Mã hóa dữ liệu cũ + điền blind index.
        foreach (DB::table('KHACH_HANG')->get() as $r) {
            $plainSdt = Pii::tryDecrypt($r->sdt);
            DB::table('KHACH_HANG')->where('ma_kh', $r->ma_kh)->update([
                'ten_kh'   => Pii::encIfPlain($r->ten_kh),
                'sdt'      => Pii::encIfPlain($r->sdt),
                'sdt_hash' => Pii::phoneHash($plainSdt),
            ]);
        }
        foreach (DB::table('NHAN_VIEN')->get() as $r) {
            DB::table('NHAN_VIEN')->where('ma_nv', $r->ma_nv)->update([
                'email'   => Pii::encIfPlain($r->email),
                'sdt'     => Pii::encIfPlain($r->sdt),
                'cccd'    => Pii::encIfPlain($r->cccd ?? null),
                'dia_chi' => Pii::encIfPlain($r->dia_chi ?? null),
            ]);
        }
        foreach (DB::table('ORDERS')->whereNotNull('ten_khach')->orWhereNotNull('sdt_khach')->get() as $r) {
            DB::table('ORDERS')->where('ma_order', $r->ma_order)->update([
                'ten_khach' => Pii::encIfPlain($r->ten_khach),
                'sdt_khach' => Pii::encIfPlain($r->sdt_khach),
            ]);
        }
        foreach (DB::table('NHA_CUNG_CAP')->get() as $r) {
            DB::table('NHA_CUNG_CAP')->where('ma_ncc', $r->ma_ncc)->update([
                'sdt'     => Pii::encIfPlain($r->sdt),
                'email'   => Pii::encIfPlain($r->email),
                'dia_chi' => Pii::encIfPlain($r->dia_chi),
            ]);
        }
    }

    public function down(): void
    {
        // Giải mã trả lại plaintext trước khi thu nhỏ cột (tránh mất dữ liệu).
        foreach (DB::table('KHACH_HANG')->get() as $r) {
            DB::table('KHACH_HANG')->where('ma_kh', $r->ma_kh)->update([
                'ten_kh' => Pii::tryDecrypt($r->ten_kh),
                'sdt'    => Pii::tryDecrypt($r->sdt),
            ]);
        }
        foreach (DB::table('NHAN_VIEN')->get() as $r) {
            DB::table('NHAN_VIEN')->where('ma_nv', $r->ma_nv)->update([
                'email'   => Pii::tryDecrypt($r->email),
                'sdt'     => Pii::tryDecrypt($r->sdt),
                'cccd'    => Pii::tryDecrypt($r->cccd ?? null),
                'dia_chi' => Pii::tryDecrypt($r->dia_chi ?? null),
            ]);
        }
        foreach (DB::table('ORDERS')->get() as $r) {
            DB::table('ORDERS')->where('ma_order', $r->ma_order)->update([
                'ten_khach' => Pii::tryDecrypt($r->ten_khach),
                'sdt_khach' => Pii::tryDecrypt($r->sdt_khach),
            ]);
        }
        foreach (DB::table('NHA_CUNG_CAP')->get() as $r) {
            DB::table('NHA_CUNG_CAP')->where('ma_ncc', $r->ma_ncc)->update([
                'sdt'     => Pii::tryDecrypt($r->sdt),
                'email'   => Pii::tryDecrypt($r->email),
                'dia_chi' => Pii::tryDecrypt($r->dia_chi),
            ]);
        }

        Schema::table('KHACH_HANG', function (Blueprint $table) {
            $table->dropIndex(['sdt_hash']);
            $table->dropColumn('sdt_hash');
        });
    }
};
