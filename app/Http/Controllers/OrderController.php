<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    // ── STAFF ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $status     = $request->get('status', 'cho_xac_nhan');
        $maChiNhanh = session('ma_chi_nhanh');

        $orders = Order::with(['ban', 'chiTietOrders.mon', 'khachHang'])
            ->where('ma_chi_nhanh', $maChiNhanh)
            ->where('trang_thai', $status)
            ->orderByDesc('ngay_order')
            ->orderByDesc('gio_order')
            ->paginate(12);

        $counts = $this->orderService->countByStatus($maChiNhanh);

        return view('staff.order-list', compact('orders', 'counts', 'status'));
    }

    /** JSON polling cho order board — gọi bằng fetch mỗi 10s */
    public function apiList()
    {
        $maChiNhanh = session('ma_chi_nhanh');

        $orders = Order::with(['ban', 'chiTietOrders.mon'])
            ->where('ma_chi_nhanh', $maChiNhanh)
            ->whereIn('trang_thai', ['cho_xac_nhan','da_xac_nhan','dang_pha_che','da_phuc_vu'])
            ->where('ngay_order', now()->toDateString())
            ->orderByDesc('gio_order')
            ->get()
            ->map(fn($o) => [
                'ma_order'   => $o->ma_order,
                'so_ban'     => $o->ban?->so_ban,
                'trang_thai' => $o->trang_thai,
                'gio_order'  => $o->gio_order ? Carbon::parse($o->gio_order)->format('H:i') : '',
                'total'      => $o->chiTietOrders->sum(fn($i) => $i->don_gia_tai_thoi_diem * $i->so_luong),
                'items'      => $o->chiTietOrders->map(fn($i) => [
                    'name'  => $i->mon?->ten_mon ?? '—',
                    'qty'   => $i->so_luong,
                    'price' => $i->don_gia_tai_thoi_diem,
                ]),
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function confirm(string $maOrder)
    {
        $this->orderService->confirm($maOrder);
        if (request()->expectsJson()) return response()->json(['ok' => true]);
        return back()->with('success', 'Đã xác nhận đơn hàng.');
    }

    /** FIX: cho phép tất cả trạng thái hợp lệ, trả JSON khi được yêu cầu */
    public function updateStatus(Request $request, string $maOrder)
    {
        $request->validate([
            'trang_thai' => 'required|in:da_xac_nhan,dang_pha_che,da_phuc_vu,hoan_thanh,da_huy',
        ]);

        $this->orderService->updateStatus($maOrder, $request->trang_thai);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'trang_thai' => $request->trang_thai]);
        }
        return back()->with('success', 'Cập nhật trạng thái thành công.');
    }

    public function show(string $maOrder)
    {
        $order = Order::with(['ban', 'chiTietOrders.mon', 'khachHang'])->findOrFail($maOrder);
        return view('staff.order-detail', compact('order'));
    }

    public function merge(Request $request, string $maOrder)
    {
        $request->validate(['target_order' => 'required|string']);
        $this->orderService->merge($maOrder, $request->target_order);
        return back()->with('success', 'Đã gộp đơn hàng.');
    }

    public function split(Request $request, string $maOrder)
    {
        $request->validate([
            'ma_mon'        => 'required|string',
            'so_luong_tach' => 'required|integer|min:1',
        ]);

        try {
            $maOrderMoi = $this->orderService->split(
                $maOrder, $request->ma_mon, (int) $request->so_luong_tach
            );
            return back()->with('success', "Đã tách thành order mới: {$maOrderMoi}");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['split' => $e->getMessage()]);
        }
    }

    // ── CUSTOMER ──────────────────────────────────────────────

    public function createFromQr(StoreOrderRequest $request, string $maBan)
    {
        $result = $this->orderService->createOrder(
            maBan:      $maBan,
            tenKh:      $request->ten_kh ?: 'Khách',
            sdtKh:      $request->sdt_kh,
            maChiNhanh: \App\Models\Ban::findOrFail($maBan)->ma_chi_nhanh,
        );

        // JS fetch → JSON; form POST → redirect
        if ($request->expectsJson()) {
            return response()->json([
                'ma_order' => $result['ma_order'],
                'ma_ban'   => $maBan,
            ]);
        }

        return redirect()->route('customer.menu', [
            'ma_ban'   => $maBan,
            'ma_order' => $result['ma_order'],
        ]);
    }

    public function addItem(Request $request, string $maOrder)
    {
        $request->validate([
            'ma_mon'   => 'required|string',
            'so_luong' => 'nullable|integer|min:1',
            'ghi_chu'  => 'nullable|string|max:200',
            'options'  => 'nullable|array|max:20',
            'options.*.type' => 'required_with:options|string|max:30',
            'options.*.label' => 'nullable|string|max:50',
            'options.*.value' => 'required_with:options|string|max:100',
            'options.*.price' => 'nullable|numeric|min:0',
        ]);
        $this->orderService->addItem(
            $maOrder,
            $request->ma_mon,
            $request->so_luong ?? 1,
            $request->ghi_chu,
            $request->input('options', [])
        );

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return back();
    }

    public function removeItem(string $maOrder, string $maMon)
    {
        $this->orderService->removeItem($maOrder, $maMon);
        if (request()->expectsJson()) return response()->json(['ok' => true]);
        return back();
    }

    public function showCart(string $maOrder)
    {
        $order = Order::with(['ban', 'chiTietOrders.mon'])->findOrFail($maOrder);
        return view('customer.checkout', compact('order'));
    }

    public function confirmByCustomer(string $maOrder)
    {
        $order = Order::where('ma_order', $maOrder)->where('trang_thai', 'cho_xac_nhan')->first();
        if (!$order) return redirect()->route('customer.status', $maOrder);
        return redirect()->route('customer.status', $maOrder)
            ->with('info', 'Đơn hàng đã được gửi. Vui lòng chờ nhân viên xác nhận.');
    }

    public function status(string $maOrder)
    {
        $order = Order::findOrFail($maOrder);
        return view('customer.status', compact('order'));
    }

    /** JSON polling cho trang trạng thái khách */
    public function statusJson(string $maOrder)
    {
        $order = Order::findOrFail($maOrder);
        return response()->json([
            'ma_order'   => $order->ma_order,
            'trang_thai' => $order->trang_thai,
        ]);
    }
}
