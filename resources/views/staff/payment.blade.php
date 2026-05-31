@extends('layouts.app')
@section('page-title', 'Thanh toán — Bàn ' . $order->ban->so_ban)

@section('content')
<div class="max-w-md">
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-4">
        <h2 class="font-medium mb-4">Chi tiết đơn #{{ $order->ma_order }}</h2>
        <div class="space-y-2 mb-4">
            @foreach($order->chiTietOrders as $item)
            <div class="flex justify-between text-sm">
                <span>{{ $item->mon->ten_mon }} x{{ $item->so_luong }}</span>
                <span class="text-gray-600">{{ number_format($item->don_gia_tai_thoi_diem * $item->so_luong, 0, ',', '.') }}đ</span>
            </div>
            @endforeach
            <div class="border-t border-gray-100 pt-2 flex justify-between font-medium">
                <span>Tổng</span>
                <span>{{ number_format($tongTien, 0, ',', '.') }}đ</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('payment.process', $order->ma_order) }}"
          class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-xs text-gray-500 mb-1">Chiết khấu (%)</label>
            <input type="number" name="chiet_khau" value="0" min="0" max="100" step="1"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Phương thức thanh toán</label>
            <select name="phuong_thuc_tt" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                <option value="tien_mat">Tiền mặt</option>
                <option value="chuyen_khoan">Chuyển khoản</option>
                <option value="momo">MoMo</option>
                <option value="vnpay">VNPay</option>
            </select>
        </div>
        <button type="submit"
                class="w-full bg-amber-500 text-white rounded-xl py-3 text-sm font-medium">
            Xác nhận thanh toán
        </button>
    </form>
</div>
@endsection
