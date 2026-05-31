@props(['order'])
<div class="flex min-h-56 flex-col gap-4 rounded-[1.5rem] bg-white p-5 ring-1 ring-[#522C25]/10 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-[#522C25]/10">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/50">{{ $order->ban ? 'Bàn ' . $order->ban->so_ban : 'Mang về' }}</p>
            <p class="mt-1 text-xl font-semibold text-[#1A1A1A]">{{ $order->gio_order->format('H:i') }}</p>
            <p class="mt-1 font-mono text-xs text-[#522C25]/60">{{ $order->ma_order }}</p>
        </div>
        <x-order-status-badge :status="$order->trang_thai" />
    </div>
    <div class="space-y-1 text-sm text-[#522C25]/70">
        @foreach($order->chiTietOrders->take(3) as $item)
        <p class="flex justify-between gap-3"><span class="line-clamp-1">{{ $item->mon->ten_mon }}</span><span class="font-semibold">x{{ $item->so_luong }}</span></p>
        @if($item->ghi_chu)
            <p class="line-clamp-1 text-xs text-[#522C25]/45">{{ $item->ghi_chu }}</p>
        @endif
        @endforeach
        @if($order->chiTietOrders->count() > 3)
        <p class="text-[#522C25]/45">+ {{ $order->chiTietOrders->count() - 3 }} món khác</p>
        @endif
    </div>
    <div class="mt-auto flex gap-2 pt-2">
        <a href="{{ route('orders.show', $order->ma_order) }}"
           class="flex-1 rounded-full bg-[#F2F2F2] py-2 text-center text-xs font-semibold text-[#522C25]">
            Chi tiết
        </a>
        @if($order->trang_thai === 'cho_xac_nhan' && $order->ban)
        <form method="POST" action="{{ route('orders.confirm', $order->ma_order) }}" class="flex-1">
            @csrf @method('PUT')
            <button class="w-full rounded-full bg-[#E82C2A] py-2 text-xs font-semibold text-white">
                Xác nhận
            </button>
        </form>
        @endif
        @if(!$order->ban || in_array($order->trang_thai, ['da_xac_nhan','dang_pha_che','da_phuc_vu']))
        <a href="{{ route('payment.show', $order->ma_order) }}"
           class="flex-1 rounded-full bg-[#52613B] py-2 text-center text-xs font-semibold text-white">
            Thanh toán
        </a>
        @endif
    </div>
</div>
