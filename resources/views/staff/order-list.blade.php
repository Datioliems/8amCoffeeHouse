@extends('layouts.app')
@section('page-title', 'Quản lý đơn hàng')

@section('content')
<div class="max-w-7xl space-y-5">
    <div class="rounded-[2rem] bg-white p-4 ring-1 ring-[#522C25]/10 am-shadow">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Order board</p>
                <h2 class="mt-1 text-2xl font-semibold">Luồng xử lý đơn tại quầy</h2>
            </div>
            <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                @foreach(['cho_xac_nhan' => 'Chờ xác nhận', 'da_xac_nhan' => 'Đã xác nhận', 'dang_pha_che' => 'Đang pha chế', 'da_phuc_vu' => 'Đã phục vụ'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                   class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold transition
                          {{ request('status', 'cho_xac_nhan') === $val ? 'bg-[#1A1A1A] text-white' : 'bg-[#F2F2F2] text-[#522C25]' }}">
                    {{ $label }}
                    <span class="ml-1 rounded-full bg-white/20 px-1.5 py-0.5">{{ $counts[$val] ?? 0 }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($orders as $order)
            <x-order-card :order="$order" />
        @empty
            <div class="col-span-full rounded-[2rem] border border-dashed border-[#522C25]/20 bg-white py-16 text-center text-sm text-[#522C25]/55">
                Không có đơn hàng nào trong trạng thái này.
            </div>
        @endforelse
    </div>

    <div>{{ $orders->links() }}</div>
</div>
@endsection
