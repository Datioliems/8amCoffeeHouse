<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Dọn các tài khoản nhân viên TẠO NHƯNG CHƯA KÍCH HOẠT (email không tồn tại /
 * không xác nhận) sau N ngày. Xoá cả TAI_KHOAN lẫn NHAN_VIEN tạo kèm.
 */
class PurgeUnconfirmedAccounts extends Command
{
    protected $signature = 'accounts:purge-unconfirmed {--days= : Số ngày quá hạn (mặc định lấy từ config)}';
    protected $description = 'Xoa tai khoan nhan vien chua kich hoat qua N ngay';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('security.purge_unconfirmed_days', 7));
        $moc  = now()->subDays($days);

        $accounts = DB::table('TAI_KHOAN')
            ->where('trang_thai', 'cho_xac_minh')
            ->whereNotNull('tao_luc')
            ->where('tao_luc', '<', $moc)
            ->get(['ma_tai_khoan', 'ma_nv', 'ten_tk']);

        if ($accounts->isEmpty()) {
            $this->info("Khong co tai khoan cho_xac_minh nao qua {$days} ngay.");
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($accounts as $acc) {
            DB::transaction(function () use ($acc, &$count) {
                DB::table('TAI_KHOAN')->where('ma_tai_khoan', $acc->ma_tai_khoan)->delete();
                // Xoá nhân viên tạo kèm nếu không còn tài khoản nào khác trỏ tới.
                $conLai = DB::table('TAI_KHOAN')->where('ma_nv', $acc->ma_nv)->count();
                if ($conLai === 0) {
                    DB::table('NHAN_VIEN')->where('ma_nv', $acc->ma_nv)->delete();
                }
                $count++;
            });
            $this->line("  - Xoa {$acc->ten_tk} ({$acc->ma_tai_khoan})");
        }

        $this->info("Da xoa {$count} tai khoan chua kich hoat qua {$days} ngay.");
        return self::SUCCESS;
    }
}
