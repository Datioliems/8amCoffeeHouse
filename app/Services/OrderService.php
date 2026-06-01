<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderLog;
use App\Models\ChiTietOrder;
use App\Models\ChiTietOrderOption;
use App\Models\KhachHang;
use App\Models\Mon;
use App\Services\MenuAvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(string $maBan, string $tenKh, ?string $sdtKh, string $maChiNhanh): array
    {
        return DB::transaction(function () use ($maBan, $tenKh, $sdtKh, $maChiNhanh) {
            // Chưa tạo KHACH_HANG ở bước này — chỉ lưu tạm tên/SĐT trên đơn.
            // Khách chỉ được lưu vào danh sách khi đã có giao dịch (thanh toán).
            $maOrder = $this->generateUniqueOrderId();

            Order::create([
                'ma_order'     => $maOrder,
                'ma_ban'       => $maBan,
                'ma_kh'        => null,
                'ten_khach'    => $tenKh,
                'sdt_khach'    => $sdtKh,
                'ma_chi_nhanh' => $maChiNhanh,
                'trang_thai'   => 'dang_chon',
                'ngay_order'   => now()->toDateString(),
                'gio_order'    => now()->toTimeString(),
            ]);

            $this->log($maOrder, 'tao_don_nhap', null, 'dang_chon', 'Khach hang bat dau chon mon tu QR.', [
                'ma_ban' => $maBan,
                'ma_chi_nhanh' => $maChiNhanh,
            ]);

            return ['ma_order' => $maOrder, 'ma_kh' => null];
        });
    }

    public function confirm(string $maOrder): void
    {
        DB::transaction(function () use ($maOrder) {
            // Tìm đơn trước (không lọc trạng thái) để báo lỗi mềm thay vì 404 "not found".
            $order = Order::where('ma_order', $maOrder)
                          ->lockForUpdate()
                          ->first();

            if (!$order) {
                throw ValidationException::withMessages([
                    'order' => 'Không tìm thấy đơn hàng này.',
                ]);
            }

            if ($order->trang_thai !== 'cho_xac_nhan') {
                throw ValidationException::withMessages([
                    'order' => 'Đơn này không ở trạng thái chờ xác nhận (hiện tại: ' . $this->statusLabel($order->trang_thai) . '). Có thể đơn đã được xử lý.',
                ]);
            }

            $order->loadMissing('chiTietOrders.mon.dinhMucs.nguyenLieu.tonKhos');
            $stockIssues = $this->stockIssuesForOrder($order);
            if (!empty($stockIssues)) {
                throw ValidationException::withMessages([
                    'stock' => 'Không thể xác nhận đơn vì thiếu nguyên liệu: ' . implode('; ', $stockIssues) . '. Vui lòng báo khách, điều chỉnh đơn hoặc ẩn món đang hết nguyên liệu.',
                ]);
            }

            $order->update([
                'trang_thai'         => 'da_xac_nhan',
                'thoi_gian_xac_nhan' => now(),
            ]);
            $this->log($maOrder, 'xac_nhan_don', 'cho_xac_nhan', 'da_xac_nhan', 'Nhan vien xac nhan don hang.', maNv: session('ma_nv'));
        });
    }

    /** Nhãn tiếng Việt cho trạng thái đơn (dùng cho thông báo lỗi/UI). */
    public static function statusLabel(string $trangThai): string
    {
        return [
            'dang_chon'    => 'Đang chọn món',
            'cho_xac_nhan' => 'Chờ xác nhận',
            'da_xac_nhan'  => 'Đã xác nhận',
            'dang_pha_che' => 'Đang pha chế',
            'da_phuc_vu'   => 'Đã phục vụ',
            'hoan_thanh'   => 'Đã thanh toán',
            'da_huy'       => 'Đã hủy',
        ][$trangThai] ?? $trangThai;
    }

    public function createTakeawayOrder(string $tenKh, ?string $sdtKh, string $maChiNhanh, array $items): string
    {
        return DB::transaction(function () use ($tenKh, $sdtKh, $maChiNhanh, $items) {
            // Khách chỉ được lưu vào danh sách khi thanh toán — ở đây chỉ lưu tạm trên đơn.
            $maOrder = $this->generateUniqueOrderId();

            Order::create([
                'ma_order' => $maOrder,
                'ma_ban' => null,
                'ma_kh' => null,
                'ten_khach' => $tenKh ?: 'Khách mang về',
                'sdt_khach' => $sdtKh,
                'ma_chi_nhanh' => $maChiNhanh,
                'trang_thai' => 'cho_xac_nhan',
                'ngay_order' => now()->toDateString(),
                'gio_order' => now()->toTimeString(),
                'ghi_chu' => 'Đơn mang về',
            ]);

            foreach ($items as $item) {
                $quantity = (int) ($item['so_luong'] ?? 0);
                if ($quantity <= 0 || empty($item['ma_mon'])) {
                    continue;
                }

                $mon = Mon::where('ma_mon', $item['ma_mon'])
                    ->where('trang_thai', 'active')
                    ->firstOrFail();

                $chiTiet = ChiTietOrder::create([
                    'ma_order' => $maOrder,
                    'ma_mon' => $mon->ma_mon,
                    'so_luong' => $quantity,
                    'don_gia_tai_thoi_diem' => $mon->don_gia,
                    'ghi_chu' => $item['ghi_chu'] ?? null,
                ]);

                if (!empty($item['options'])) {
                    $this->syncOrderOptions($chiTiet->id, $maOrder, $mon->ma_mon, $item['options']);
                }
            }

            $this->log($maOrder, 'tao_don_mang_ve', null, 'cho_xac_nhan', 'Nhan vien tao don mua mang ve.', [
                'so_dong_mon' => count($items),
            ]);

            return $maOrder;
        });
    }

    public function submitByCustomer(string $maOrder): void
    {
        DB::transaction(function () use ($maOrder) {
            $order = Order::where('ma_order', $maOrder)
                          ->where('trang_thai', 'dang_chon')
                          ->lockForUpdate()
                          ->firstOrFail();

            $order->update(['trang_thai' => 'cho_xac_nhan']);
            $this->log($maOrder, 'khach_gui_don', 'dang_chon', 'cho_xac_nhan', 'Khach hang gui don cho quan.');
        });
    }

    public function updateStatus(string $maOrder, string $trangThai): void
    {
        DB::transaction(function () use ($maOrder, $trangThai) {
            $order = Order::where('ma_order', $maOrder)->lockForUpdate()->firstOrFail();
            $oldStatus = $order->trang_thai;

            $changes = ['trang_thai' => $trangThai];
            // Đóng dấu mốc thời gian theo trạng thái
            if ($trangThai === 'da_xac_nhan' && !$order->thoi_gian_xac_nhan) {
                $changes['thoi_gian_xac_nhan'] = now();
            }
            if ($trangThai === 'da_phuc_vu' && !$order->thoi_gian_phuc_vu) {
                $changes['thoi_gian_phuc_vu'] = now();
            }

            $order->update($changes);
            $this->log($maOrder, 'cap_nhat_trang_thai', $oldStatus, $trangThai, 'Cap nhat trang thai don hang.', maNv: session('ma_nv'));
        });
    }

    public function addItem(string $maOrder, string $maMon, int $soLuong, ?string $ghiChu = null, array $options = []): void
    {
        DB::transaction(function () use ($maOrder, $maMon, $soLuong, $ghiChu, $options) {
            Order::where('ma_order', $maOrder)
                ->where('trang_thai', 'dang_chon')
                ->lockForUpdate()
                ->firstOrFail();

            $existing = ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->lockForUpdate()->first();
            $mon = Mon::findOrFail($maMon);

            if ($mon->trang_thai !== 'active') {
                throw ValidationException::withMessages([
                    'ma_mon' => 'Món này hiện không thể đặt.',
                ]);
            }

            if ($existing) {
                $existing->increment('so_luong', $soLuong);
                // Chỉ ghi đè ghi_chu khi có giá trị mới và khác nội dung cũ
                if ($ghiChu && $ghiChu !== $existing->ghi_chu) {
                    $existing->update(['ghi_chu' => mb_substr($ghiChu, 0, 200)]);
                }
                $chiTietId = $existing->id;
                $action = 'tang_so_luong_mon';
            } else {
                $chiTiet = ChiTietOrder::create([
                    'ma_order'              => $maOrder,
                    'ma_mon'                => $maMon,
                    'so_luong'              => $soLuong,
                    'don_gia_tai_thoi_diem' => $mon->don_gia,
                    'ghi_chu'               => $ghiChu,
                ]);
                $chiTietId = $chiTiet->id;
                $action = 'them_mon';
            }

            $this->syncOrderOptions($chiTietId, $maOrder, $maMon, $options);
            $this->log($maOrder, $action, data: [
                'ma_mon' => $maMon,
                'so_luong' => $soLuong,
                'ghi_chu' => $ghiChu,
                'options' => $options,
            ]);
        });
    }

    private function syncOrderOptions(int $chiTietId, string $maOrder, string $maMon, array $options): void
    {
        if (empty($options)) {
            return;
        }

        ChiTietOrderOption::where('chi_tiet_id', $chiTietId)->delete();

        foreach ($options as $option) {
            ChiTietOrderOption::create([
                'chi_tiet_id'  => $chiTietId,
                'ma_order'     => $maOrder,
                'ma_mon'       => $maMon,
                'loai_option'  => (string) ($option['type'] ?? 'custom'),
                'ten_lua_chon' => (string) ($option['value'] ?? ''),
                'gia_them'     => (int) ($option['price'] ?? 0),
            ]);
        }
    }

    public function removeItem(string $maOrder, string $maMon): void
    {
        DB::transaction(function () use ($maOrder, $maMon) {
            $item = ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->lockForUpdate()->first();
            ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->delete();
            $this->log($maOrder, 'xoa_mon', data: [
                'ma_mon' => $maMon,
                'so_luong' => $item?->so_luong,
            ]);
        });
    }

    private function stockIssuesForOrder(Order $order): array
    {
        $availability = app(MenuAvailabilityService::class);

        return $order->chiTietOrders
            ->map(function ($item) use ($availability, $order) {
                if (!$item->mon) {
                    return null;
                }

                $missing = $availability->unavailableIngredients($item->mon, $order->ma_chi_nhanh, (int) $item->so_luong);

                return empty($missing)
                    ? null
                    : ($item->mon->ten_mon . ' thiếu ' . implode(', ', $missing));
            })
            ->filter()
            ->values()
            ->all();
    }

    public function merge(string $maOrderGoc, string $maOrderTarget): void
    {
        DB::transaction(function () use ($maOrderGoc, $maOrderTarget) {
            $orderGoc = Order::where('ma_order', $maOrderGoc)->lockForUpdate()->firstOrFail();
            $target   = Order::where('ma_order', $maOrderTarget)->lockForUpdate()->firstOrFail();

            if ($orderGoc->ma_chi_nhanh !== $target->ma_chi_nhanh) {
                throw new \InvalidArgumentException('Không thể gộp đơn của hai chi nhánh khác nhau.');
            }

            $targetItems = ChiTietOrder::where('ma_order', $maOrderTarget)
                ->lockForUpdate()->get();

            foreach ($targetItems as $item) {
                $existing = ChiTietOrder::where('ma_order', $maOrderGoc)
                    ->where('ma_mon', $item->ma_mon)->lockForUpdate()->first();

                if ($existing) {
                    $existing->increment('so_luong', $item->so_luong);
                    // Xóa dòng target (cascade sẽ xóa options)
                    $item->delete();
                } else {
                    $item->update(['ma_order' => $maOrderGoc]);
                }
            }

            $oldStatus = $target->trang_thai;
            $target->update(['trang_thai' => 'da_huy']);
            $this->log($maOrderGoc, 'gop_don_nhan', data: ['ma_order_gop' => $maOrderTarget]);
            $this->log($maOrderTarget, 'gop_don_huy', $oldStatus, 'da_huy', 'Don duoc gop vao don khac.', [
                'ma_order_nhan' => $maOrderGoc,
            ]);
        });
    }

    public function split(string $maOrderGoc, string $maMon, int $soLuongTach): string
    {
        return DB::transaction(function () use ($maOrderGoc, $maMon, $soLuongTach) {
            $itemGoc = ChiTietOrder::where('ma_order', $maOrderGoc)->where('ma_mon', $maMon)
                ->lockForUpdate()->firstOrFail();

            if ($soLuongTach >= $itemGoc->so_luong) {
                throw new \InvalidArgumentException('Số lượng tách phải nhỏ hơn số lượng hiện tại.');
            }

            $orderGoc   = Order::findOrFail($maOrderGoc);
            $maOrderMoi = $this->generateUniqueOrderId();

            Order::create([
                'ma_order'     => $maOrderMoi,
                'ma_ban'       => $orderGoc->ma_ban,
                'ma_kh'        => $orderGoc->ma_kh,
                'ma_chi_nhanh' => $orderGoc->ma_chi_nhanh,
                'trang_thai'   => $orderGoc->trang_thai,
                'ngay_order'   => now()->toDateString(),
                'gio_order'    => now()->toTimeString(),
            ]);

            $chiTietMoi = ChiTietOrder::create([
                'ma_order'              => $maOrderMoi,
                'ma_mon'                => $maMon,
                'so_luong'              => $soLuongTach,
                'don_gia_tai_thoi_diem' => $itemGoc->don_gia_tai_thoi_diem,
                'ghi_chu'               => $itemGoc->ghi_chu,
            ]);

            // Copy options theo chi_tiet_id — tránh tính gấp đôi phụ thu
            $options = ChiTietOrderOption::where('chi_tiet_id', $itemGoc->id)
                ->get(['loai_option', 'ten_lua_chon', 'gia_them']);

            foreach ($options as $option) {
                ChiTietOrderOption::create([
                    'chi_tiet_id'  => $chiTietMoi->id,
                    'ma_order'     => $maOrderMoi,
                    'ma_mon'       => $maMon,
                    'loai_option'  => $option->loai_option,
                    'ten_lua_chon' => $option->ten_lua_chon,
                    'gia_them'     => $option->gia_them,
                ]);
            }

            $itemGoc->decrement('so_luong', $soLuongTach);

            $this->log($maOrderGoc, 'tach_don_goc', data: [
                'ma_order_moi' => $maOrderMoi,
                'ma_mon' => $maMon,
                'so_luong_tach' => $soLuongTach,
            ]);
            $this->log($maOrderMoi, 'tach_don_moi', null, $orderGoc->trang_thai, 'Tao don moi tu thao tac tach don.', [
                'ma_order_goc' => $maOrderGoc,
                'ma_mon' => $maMon,
                'so_luong_tach' => $soLuongTach,
            ]);

            return $maOrderMoi;
        });
    }

    public function countByStatus(string $maChiNhanh): array
    {
        return Order::where('ma_chi_nhanh', $maChiNhanh)
            ->whereIn('trang_thai', ['cho_xac_nhan','da_xac_nhan','dang_pha_che','da_phuc_vu','hoan_thanh'])
            ->selectRaw('trang_thai, COUNT(*) as cnt')
            ->groupBy('trang_thai')
            ->pluck('cnt', 'trang_thai')
            ->toArray();
    }

    public function log(
        string $maOrder,
        string $hanhDong,
        ?string $trangThaiCu = null,
        ?string $trangThaiMoi = null,
        ?string $noiDung = null,
        ?array $data = null,
        ?string $maNv = null
    ): void {
        OrderLog::create([
            'ma_order' => $maOrder,
            'hanh_dong' => $hanhDong,
            'trang_thai_cu' => $trangThaiCu,
            'trang_thai_moi' => $trangThaiMoi,
            'noi_dung' => $noiDung,
            'du_lieu' => $data,
            'ma_nv' => $maNv ?? session('ma_nv'),
            'created_at' => now(),
        ]);
    }

    /**
     * Đảm bảo đơn có KHACH_HANG (gọi khi thanh toán — thời điểm phát sinh giao dịch).
     * Tra theo SĐT tạm trên đơn; tạo mới nếu chưa có. Gắn ma_kh vào đơn.
     */
    public function ensureCustomerForOrder(Order $order): ?string
    {
        if ($order->ma_kh) {
            return $order->ma_kh;
        }

        $tenKh = $order->ten_khach ?: 'Khách lẻ';
        $sdtKh = $order->sdt_khach;

        $khachHang = null;
        if ($sdtKh) {
            $khachHang = KhachHang::where('sdt', $sdtKh)->first();
        }
        if (!$khachHang) {
            $khachHang = $this->createKhachHang($tenKh, $sdtKh);
        }

        $order->update(['ma_kh' => $khachHang->ma_kh]);
        return $khachHang->ma_kh;
    }

    private function createKhachHang(string $tenKh, ?string $sdtKh): KhachHang
    {
        $attempts = 0;
        while (true) {
            try {
                $max = KhachHang::query()
                    ->selectRaw("MAX(CAST(SUBSTRING(ma_kh, 3) AS UNSIGNED)) as max_code")
                    ->value('max_code') ?? 0;
                $maKh = 'KH' . str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);

                return KhachHang::create([
                    'ma_kh'  => $maKh,
                    'ten_kh' => $tenKh,
                    'sdt'    => $sdtKh,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // errorInfo[1] === 1062: Duplicate entry
                if ($attempts++ >= 3 || $e->errorInfo[1] !== 1062) {
                    throw $e;
                }
            }
        }
    }

    private function generateUniqueOrderId(): string
    {
        do {
            $maOrder = 'ORD' . now()->format('ymdHis') . random_int(10, 99);
        } while (Order::whereKey($maOrder)->exists());

        return $maOrder;
    }
}
