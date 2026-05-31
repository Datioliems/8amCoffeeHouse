@extends('layouts.app')

@section('title', 'Chi tiết đơn - '.$order->ma_order)
@section('page-title', 'Chi tiết đơn hàng')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('orders.index') }}" class="text-gray-400 hover:text-gray-600">Quay lại</a>
        <x-order-status-badge :status="$order->trang_thai" />
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm mb-4">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Mã đơn</p>
                <p class="font-mono font-medium">{{ $order->ma_order }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Bàn số</p>
                <p class="font-medium">{{ $order->ban->so_ban ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Khách hàng</p>
                <p class="font-medium">{{ $order->khachHang->ten_kh ?? 'Khách vãng lai' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Thời gian</p>
                <p class="font-medium">{{ $order->ngay_order }} - {{ $order->gio_order }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-gray-100 font-medium text-gray-700 text-sm">Món đặt</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-2 text-left">Tên món</th>
                    <th class="px-5 py-2 text-center">SL</th>
                    <th class="px-5 py-2 text-right">Đơn giá</th>
                    <th class="px-5 py-2 text-right">Thành tiền</th>
                    <th class="px-5 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($order->chiTietOrders as $item)
                <tr>
                    <td class="px-5 py-3 font-medium text-gray-800">
                        {{ $item->mon->ten_mon ?? $item->ma_mon }}
                        @if($item->ghi_chu)
                            <p class="mt-1 whitespace-pre-line text-xs font-normal leading-5 text-gray-500">{{ $item->ghi_chu }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $item->so_luong }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($item->don_gia_tai_thoi_diem, 0, ',', '.') }}đ</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ number_format($item->don_gia_tai_thoi_diem * $item->so_luong, 0, ',', '.') }}đ
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($item->so_luong > 1)
                        <form action="{{ route('orders.split', $order->ma_order) }}" method="POST" class="flex justify-end gap-2">
                            @csrf
                            <input type="hidden" name="ma_mon" value="{{ $item->ma_mon }}">
                            <input type="number" name="so_luong_tach" min="1" max="{{ $item->so_luong - 1 }}"
                                   class="w-16 border border-gray-200 rounded-lg px-2 py-1 text-xs text-right">
                            <button type="submit" class="text-xs text-blue-500 hover:underline">Tách</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-5 py-3 text-right font-semibold text-gray-700">Tổng cộng</td>
                    <td class="px-5 py-3 text-right font-bold text-amber-600 text-base">
                        {{ number_format($order->chiTietOrders->sum(fn($i) => $i->don_gia_tai_thoi_diem * $i->so_luong), 0, ',', '.') }}đ
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($order->trang_thai === 'cho_xac_nhan')
    <div class="flex gap-3">
        <form action="{{ route('orders.confirm', $order->ma_order) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Xác nhận đơn
            </button>
        </form>
        <form action="{{ route('orders.status', $order->ma_order) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="trang_thai" value="da_huy">
            <button type="submit" onclick="return confirm('Hủy đơn này?')"
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-5 py-2 rounded-lg transition">
                Hủy đơn
            </button>
        </form>
    </div>
    @elseif($order->trang_thai === 'da_xac_nhan')
    <form action="{{ route('orders.status', $order->ma_order) }}" method="POST" class="inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="trang_thai" value="dang_pha_che">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
            Chuyển sang đang pha chế
        </button>
    </form>
    @elseif($order->trang_thai === 'dang_pha_che')
    <div class="flex gap-3">
        <form action="{{ route('orders.status', $order->ma_order) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="trang_thai" value="da_phuc_vu">
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Chuyển sang đã phục vụ
            </button>
        </form>
        <a href="{{ route('payment.show', $order->ma_order) }}"
           class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
            Thanh toán
        </a>
    </div>
    @elseif($order->trang_thai === 'da_phuc_vu')
    <a href="{{ route('payment.show', $order->ma_order) }}"
       class="inline-block bg-green-500 hover:bg-green-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
        Thanh toán
    </a>
    @endif
</div>
@endsection
