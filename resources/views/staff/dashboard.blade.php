@extends('layouts.app')

@section('title', 'Bảng điều khiển - 8AM Coffee')
@section('page-title', 'Tổng quan hôm nay')

@section('content')
@php
    $maxRevenue = max(1, $doanhThu7Ngay->max('value') ?: 1);
    $maxTopMon = max(1, $topMons->max('so_luong') ?: 1);
@endphp

<div class="max-w-7xl space-y-6">
    <section class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-[2rem] bg-[#1A1A1A] p-6 text-white am-shadow md:p-8">
            <p class="text-xs uppercase tracking-[0.2em] text-white/60">bảng vận hành 8am</p>
            <h2 class="mt-3 max-w-2xl text-4xl font-semibold leading-tight">Theo dõi đơn, bàn và doanh thu trong ca hiện tại.</h2>
            <div class="mt-7 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-xs text-white/60">Đơn hàng</p>
                    <p class="mt-2 text-3xl font-bold">{{ $orderHomNay }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-xs text-white/60">Chờ xác nhận</p>
                    <p class="mt-2 text-3xl font-bold text-[#FFB4AB]">{{ $orderChoXacNhan }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-xs text-white/60">Đang pha chế</p>
                    <p class="mt-2 text-3xl font-bold text-[#CADCAC]">{{ $orderDangPhaChe }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-xs text-white/60">Bàn có khách</p>
                    <p class="mt-2 text-3xl font-bold">{{ $banCoKhach }}<span class="text-base text-white/45">/{{ $tongBan }}</span></p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] bg-white p-6 ring-1 ring-[#522C25]/10 am-shadow md:p-8">
            <p class="text-xs uppercase tracking-[0.2em] text-[#522C25]/55">Doanh thu hôm nay</p>
            <p class="mt-4 text-5xl font-bold text-[#E82C2A]">{{ number_format($doanhThuHomNay, 0, ',', '.') }}đ</p>
            <p class="mt-4 text-sm leading-6 text-[#522C25]/65">Dữ liệu lấy từ các hóa đơn và đơn đã xử lý trong chi nhánh hiện tại.</p>
            <a href="{{ route('orders.index', ['status' => 'hoan_thanh']) }}" class="mt-7 inline-flex rounded-full bg-[#1A1A1A] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#E82C2A]">
                Mở bảng đơn hàng
            </a>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-[2rem] bg-white p-6 ring-1 ring-[#522C25]/10 am-shadow">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Biểu đồ doanh thu</p>
                    <h3 class="mt-1 text-lg font-semibold">Doanh thu 7 ngày</h3>
                </div>
                <span class="rounded-full bg-[#F2F2F2] px-3 py-1 text-xs font-semibold text-[#522C25]/70">VND</span>
            </div>
            <div class="flex h-56 items-end gap-3">
                @foreach($doanhThu7Ngay as $point)
                <div class="flex flex-1 flex-col items-center gap-2">
                    <div class="flex h-40 w-full items-end rounded-xl bg-[#F6F3F2] px-2">
                        <div class="w-full rounded-t-lg bg-[#E82C2A]" style="height: {{ max(6, ($point['value'] / $maxRevenue) * 100) }}%"></div>
                    </div>
                    <p class="text-[11px] font-medium text-[#522C25]/65">{{ $point['label'] }}</p>
                    <p class="text-[10px] text-[#522C25]/45">{{ number_format($point['value'] / 1000, 0, ',', '.') }}k</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[2rem] bg-white p-6 ring-1 ring-[#522C25]/10 am-shadow">
            <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Món nổi bật</p>
            <h3 class="mt-1 text-lg font-semibold">Món bán chạy hôm nay</h3>
            <div class="mt-5 space-y-4">
                @forelse($topMons as $mon)
                <div>
                    <div class="mb-1 flex justify-between text-xs">
                        <span class="font-medium text-[#522C25]/70">{{ $mon->ten_mon }}</span>
                        <span class="text-[#522C25]/50">{{ $mon->so_luong }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-[#F2F2F2]">
                        <div class="h-full rounded-full bg-[#CADCAC]" style="width: {{ max(8, ($mon->so_luong / $maxTopMon) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-[#522C25]/55">Chưa có dữ liệu món hôm nay.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] bg-white ring-1 ring-[#522C25]/10 am-shadow">
        <div class="flex items-center justify-between gap-4 border-b border-[#522C25]/10 px-5 py-4 md:px-6">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Hàng chờ</p>
                <h3 class="mt-1 text-lg font-semibold">Đơn cần xử lý</h3>
            </div>
            <a href="{{ route('orders.index') }}" class="rounded-full bg-[#F2F2F2] px-4 py-2 text-sm font-semibold text-[#522C25]">Xem tất cả</a>
        </div>
        <div class="divide-y divide-[#522C25]/10">
            @forelse($orderGanDay as $order)
            <div class="flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between md:px-6">
                <div>
                    <p class="font-mono text-sm font-semibold text-[#1A1A1A]">{{ $order->ma_order }}</p>
                    <p class="mt-1 text-sm text-[#522C25]/65">Bàn {{ $order->ban->so_ban ?? '?' }} · {{ $order->customer_name ?? 'Khách' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-order-status-badge :status="$order->trang_thai" />
                    <a href="{{ route('orders.show', $order->ma_order) }}"
                       class="rounded-full bg-[#1A1A1A] px-4 py-2 text-xs font-semibold text-white">
                        Chi tiết
                    </a>
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center text-sm text-[#522C25]/55">Không có đơn nào cần xử lý.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
