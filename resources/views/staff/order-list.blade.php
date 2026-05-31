@extends('layouts.app')
@section('page-title', 'Quản lý đơn hàng')

@section('content')
@php
    $statusTabs = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan' => 'Đã xác nhận',
        'dang_pha_che' => 'Đang pha chế',
        'da_phuc_vu' => 'Đã phục vụ',
        'hoan_thanh' => 'Đã thanh toán',
    ];
@endphp

<div class="max-w-7xl space-y-5">
    <div class="rounded-[2rem] bg-white p-4 ring-1 ring-[#522C25]/10 am-shadow">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Order board</p>
                <h2 class="mt-1 text-2xl font-semibold">Luồng xử lý đơn tại quầy</h2>
            </div>
            <div class="flex flex-wrap gap-2 overflow-x-auto scrollbar-hide">
                <a href="{{ route('orders.takeaway.create') }}"
                   class="whitespace-nowrap rounded-full bg-[#8B5A2B] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#6F4621]">
                    + Đơn mang về
                </a>
                @foreach($statusTabs as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                   class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold transition
                          {{ $status === $val ? 'bg-[#1A1A1A] text-white' : 'bg-[#F2F2F2] text-[#522C25] hover:bg-[#E9DDD0]' }}">
                    {{ $label }}
                    <span class="ml-1 rounded-full bg-white/20 px-1.5 py-0.5">{{ $counts[$val] ?? 0 }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($status === 'hoan_thanh')
        <div class="overflow-hidden rounded-[2rem] bg-white ring-1 ring-[#522C25]/10 am-shadow">
            <div class="flex flex-col gap-2 border-b border-[#522C25]/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Paid orders</p>
                    <h3 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Danh sách đơn đã thanh toán</h3>
                </div>
                <p class="text-sm text-[#522C25]/60">{{ $orders->total() }} hóa đơn</p>
            </div>

            <div class="divide-y divide-[#522C25]/10">
                @forelse($orders as $order)
                    @php
                        $invoice = $order->hoaDon;
                        $paidTotal = $invoice?->tong_tien_sau_ck ?? $order->chiTietOrders->sum(fn ($item) => $item->don_gia_tai_thoi_diem * $item->so_luong);
                        $methodLabels = [
                            'tien_mat' => 'Tiền mặt',
                            'chuyen_khoan' => 'Chuyển khoản',
                            'momo' => 'MoMo',
                            'vnpay' => 'VNPay',
                        ];
                    @endphp
                    <div class="grid gap-4 p-4 transition hover:bg-[#FAF7F2] lg:grid-cols-[1.15fr_0.9fr_0.8fr_0.8fr_auto] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-mono text-sm font-semibold text-[#1A1A1A]">#{{ $order->ma_order }}</p>
                                @if($invoice)
                                    <span class="rounded-full bg-[#FFF7E8] px-2.5 py-1 text-xs font-semibold text-[#8B5A2B]">{{ $invoice->ma_hoa_don }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-[#522C25]/60">{{ $order->ban ? 'Bàn ' . $order->ban->so_ban : 'Mang về' }} · {{ $order->chiTietOrders->sum('so_luong') }} món</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#522C25]/45">Khách hàng</p>
                            <p class="mt-1 truncate text-sm font-medium text-[#522C25]">{{ $order->khachHang->ho_ten ?? $order->ma_kh ?? 'Khách lẻ' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#522C25]/45">Thanh toán</p>
                            <p class="mt-1 text-sm font-medium text-[#522C25]">{{ $methodLabels[$invoice?->phuong_thuc_tt] ?? ($invoice?->phuong_thuc_tt ?? 'Đã thu') }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#522C25]/45">Tổng tiền</p>
                            <p class="mt-1 text-lg font-bold text-[#8B5A2B]">{{ number_format($paidTotal, 0, ',', '.') }}đ</p>
                        </div>

                        <div class="flex justify-start lg:justify-end">
                            <a href="{{ route('orders.show', $order->ma_order) }}"
                               class="rounded-xl bg-[#1A1A1A] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#522C25]">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center text-sm text-[#522C25]/55">
                        Chưa có đơn hàng đã thanh toán.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($orders as $order)
                <x-order-card :order="$order" />
            @empty
                <div class="col-span-full rounded-[2rem] border border-dashed border-[#522C25]/20 bg-white py-16 text-center text-sm text-[#522C25]/55">
                    Không có đơn hàng nào trong trạng thái này.
                </div>
            @endforelse
        </div>
    @endif

    <div>{{ $orders->links() }}</div>
</div>
@endsection
