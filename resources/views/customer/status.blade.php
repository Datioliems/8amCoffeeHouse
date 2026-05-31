@extends('layouts.customer')

@section('title', 'Trạng thái đơn hàng')

@section('content')
@php
    $steps = [
        'cho_xac_nhan' => ['label' => 'Chờ xác nhận', 'copy' => 'Nhân viên sẽ nhận đơn trong giây lát.', 'icon' => '01'],
        'da_xac_nhan' => ['label' => 'Đã xác nhận', 'copy' => 'Đơn đã được chuyển tới quầy pha chế.', 'icon' => '02'],
        'dang_pha_che' => ['label' => 'Đang pha chế', 'copy' => 'Đồ uống của bạn đang được chuẩn bị.', 'icon' => '03'],
        'da_phuc_vu' => ['label' => 'Đã phục vụ', 'copy' => 'Cảm ơn bạn đã ghé 8am.coffee.', 'icon' => '04'],
    ];
    $active = $steps[$order->trang_thai] ?? ['label' => 'Đơn đã hủy', 'copy' => 'Vui lòng liên hệ nhân viên để được hỗ trợ.', 'icon' => '!'];
    $keys = array_keys($steps);
    $currentIndex = array_search($order->trang_thai, $keys, true);
@endphp

<div class="mx-auto max-w-xl">
    @if(session('info'))
    <div class="mb-5 rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-700 ring-1 ring-blue-100">
        {{ session('info') }}
    </div>
    @endif

    <div class="rounded-[2rem] bg-white p-6 text-center ring-1 ring-[#522C25]/10 am-shadow md:p-8">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#E82C2A] text-2xl font-bold text-white">
            {{ $active['icon'] }}
        </div>
        <p class="am-mono mt-6 text-xs uppercase tracking-[0.16em] text-[#522C25]/55">Mã đơn {{ $order->ma_order }}</p>
        <h1 class="am-display mt-2 text-5xl leading-none text-[#1A1A1A]">{{ $active['label'] }}</h1>
        <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-[#522C25]/65">{{ $active['copy'] }}</p>

        <div class="mt-8 space-y-3 text-left">
            @foreach($steps as $status => $step)
                @php
                    $idx = array_search($status, $keys, true);
                    $done = $currentIndex !== false && $idx <= $currentIndex;
                @endphp
                <div class="flex items-center gap-3 rounded-2xl {{ $done ? 'bg-[#1A1A1A] text-white' : 'bg-[#F6F3F2] text-[#522C25]/60' }} px-4 py-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $done ? 'bg-white/15' : 'bg-white' }} text-xs font-bold">{{ $step['icon'] }}</span>
                    <span class="am-headline text-sm font-semibold">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>

        @if(!in_array($order->trang_thai, ['da_phuc_vu', 'hoan_thanh', 'da_huy']))
            <p class="mt-6 text-xs text-[#522C25]/55">Trang sẽ tự cập nhật sau 10 giây.</p>
            <meta http-equiv="refresh" content="10">
        @endif
    </div>
</div>
@endsection
