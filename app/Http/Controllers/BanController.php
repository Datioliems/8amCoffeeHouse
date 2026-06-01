<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    /** Các trạng thái order được coi là "đang mở" (bàn đang có khách). */
    private const ACTIVE_ORDER = ['cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che', 'da_phuc_vu'];

    public function index()
    {
        $maChiNhanh = session('ma_chi_nhanh');
        $bans = Ban::where('ma_chi_nhanh', $maChiNhanh)->orderBy('so_ban')->get();
        $orderCounts = $this->orderCounts($maChiNhanh);

        return view('staff.ban-list', compact('bans', 'orderCounts'));
    }

    public function update(Request $request, string $maBan)
    {
        $ban = Ban::where('ma_chi_nhanh', session('ma_chi_nhanh'))
            ->where('ma_ban', $maBan)
            ->firstOrFail();

        $validated = $request->validate([
            'so_ghe' => 'required|integer|min:1|max:20',
            'vi_tri' => 'nullable|string|max:50',
            'trang_thai' => 'required|in:trong,co_khach,dat_truoc,dong',
        ]);

        $ban->update($validated);

        return back()->with('success', 'Đã cập nhật bàn '.$ban->so_ban.'.');
    }

    public function floorplan()
    {
        return view('staff.floorplan');
    }

    /** Nhân viên tải/đổi ảnh cho 1 bàn (hiển thị ở sơ đồ 3D menu3). */
    public function uploadPhoto(Request $request, string $maBan)
    {
        $ban = Ban::where('ma_chi_nhanh', session('ma_chi_nhanh'))
            ->where('ma_ban', $maBan)
            ->firstOrFail();

        $request->validate([
            'anh' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $dir = public_path('images/tables');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        // xoá ảnh cũ (mọi đuôi) để tránh file thừa
        foreach (glob($dir.DIRECTORY_SEPARATOR.$maBan.'.*') ?: [] as $old) {
            @unlink($old);
        }

        $ext = strtolower($request->file('anh')->getClientOriginalExtension() ?: 'jpg');
        $name = $maBan.'.'.$ext;
        $request->file('anh')->move($dir, $name);

        $ban->anh = 'tables/'.$name;   // tương đối so với /images
        $ban->save();

        return back()->with('success', 'Đã cập nhật ảnh bàn '.$ban->so_ban.'.');
    }

    public function apiTables()
    {
        return $this->tablesJson(session('ma_chi_nhanh'));
    }

    public function moveTable(string $from, string $to)
    {
        return $this->doMove($from, $to, session('ma_chi_nhanh'));
    }

    public function apiTablesByBan(string $maBan)
    {
        $ban = Ban::findOrFail($maBan);

        return $this->tablesJson($ban->ma_chi_nhanh);
    }

    public function moveByBan(string $maBan, string $to)
    {
        $ban = Ban::findOrFail($maBan);

        // (b) Bàn đích phải đang TRỐNG
        $dest = Ban::where('ma_chi_nhanh', $ban->ma_chi_nhanh)->where('ma_ban', $to)->firstOrFail();
        if ($dest->trang_thai !== 'trong') {
            return response()->json(['ok' => false, 'msg' => 'Bàn đích không còn trống.'], 422);
        }

        // (a) Khách phải sở hữu 1 đơn đang ở bàn nguồn (chống đổi bàn người khác)
        $owned = (array) session('customer_orders', []);
        $ownsFrom = ! empty($owned) && DB::table('ORDERS')
            ->whereIn('ma_order', $owned)
            ->where('ma_ban', $maBan)
            ->whereIn('trang_thai', array_merge(self::ACTIVE_ORDER, ['dang_chon']))
            ->exists();
        if (! $ownsFrom) {
            return response()->json(['ok' => false, 'msg' => 'Bạn không có đơn ở bàn này để đổi.'], 403);
        }

        return $this->doMove($maBan, $to, $ban->ma_chi_nhanh);
    }

    /**
     * Ảnh từng bàn (table_TX_Y.jpg). Quy ước: F2/F3 bàn NGOÀI TRỜI = _1 (trước),
     * rồi tới bàn trong nhà; F1 đánh tuần tự cho tất cả.
     */
    private const TABLE_IMG = [
        // Tầng 1 (8 bàn, tuần tự)
        'B001' => 'table_T1_1', 'B002' => 'table_T1_2', 'B003' => 'table_T1_3', 'B004' => 'table_T1_4',
        'B005' => 'table_T1_5', 'B006' => 'table_T1_6', 'B007' => 'table_T1_7', 'B008' => 'table_T1_8',
        // Tầng 2 (ngoài trời trước: B012,B013 → _1,_2 ; trong nhà: B009-B011 → _3..)
        'B012' => 'table_T2_1', 'B013' => 'table_T2_2',
        'B009' => 'table_T2_3', 'B010' => 'table_T2_4', 'B011' => 'table_T2_5',
        // Tầng 3 (ngoài trời: B017 → _1 ; trong nhà: B014-B016 → _2..)
        'B017' => 'table_T3_1',
        'B014' => 'table_T3_2', 'B015' => 'table_T3_3', 'B016' => 'table_T3_4',
    ];

    private function tablesJson(?string $branch)
    {
        $counts = $this->orderCounts($branch);
        $bans = Ban::where('ma_chi_nhanh', $branch)->orderBy('so_ban')->get();

        return response()->json($bans->map(fn ($b) => [
            'ma_ban' => $b->ma_ban,
            'so_ban' => $b->so_ban,
            'vi_tri' => $b->vi_tri,
            'so_ghe' => $b->so_ghe,
            'trang_thai' => $b->trang_thai,
            'orders' => (int) ($counts[$b->ma_ban] ?? 0),
            'anh_ban' => $b->anh ?: (isset(self::TABLE_IMG[$b->ma_ban]) ? self::TABLE_IMG[$b->ma_ban].'.jpg' : null),
        ])->values());
    }

    private function doMove(string $from, string $to, ?string $branch)
    {
        if ($from === $to) {
            return response()->json(['ok' => false, 'msg' => 'Trùng bàn'], 422);
        }

        // Chuyển CẢ đơn đang chọn (giỏ hàng dang_chon) lẫn đơn đang mở sang bàn mới
        $move = array_merge(self::ACTIVE_ORDER, ['dang_chon']);

        DB::transaction(function () use ($from, $to, $branch, $move) {
            DB::table('ORDERS')
                ->where('ma_chi_nhanh', $branch)
                ->where('ma_ban', $from)
                ->whereIn('trang_thai', $move)
                ->update(['ma_ban' => $to]);

            Ban::where('ma_ban', $to)->update(['trang_thai' => 'co_khach']);

            $conLai = DB::table('ORDERS')
                ->where('ma_ban', $from)
                ->whereIn('trang_thai', self::ACTIVE_ORDER)
                ->count();

            Ban::where('ma_ban', $from)->update(['trang_thai' => $conLai ? 'co_khach' : 'trong']);
        });

        return response()->json(['ok' => true]);
    }

    private function orderCounts(?string $branch)
    {
        return DB::table('ORDERS')
            ->where('ma_chi_nhanh', $branch)
            ->whereIn('trang_thai', self::ACTIVE_ORDER)
            ->select('ma_ban', DB::raw('COUNT(*) as cnt'))
            ->groupBy('ma_ban')
            ->pluck('cnt', 'ma_ban');
    }
}
