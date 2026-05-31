@extends('layouts.app')
@section('page-title', 'Thanh toán - ' . ($order->ban ? 'Bàn ' . $order->ban->so_ban : 'Mang về'))

@section('content')
@php
    $bankPayload = implode('|', [
        '8AM COFFEE',
        'ORDER:' . $order->ma_order,
        'AMOUNT:' . (int) $tongTien,
        'CONTENT: THANH TOAN ' . $order->ma_order,
    ]);
    $momoPayload = 'MOMO|8AM COFFEE|ORDER:' . $order->ma_order . '|AMOUNT:' . (int) $tongTien;
@endphp

<div class="min-h-[calc(100vh-10rem)] py-4">
    <div id="payment-layout" class="mx-auto grid max-w-xl items-start gap-5 transition-all duration-300">
        <section class="rounded-2xl border border-[#522C25]/10 bg-white p-4 shadow-[0_18px_50px_rgba(82,44,37,0.08)] sm:p-5">
            <div class="flex flex-col gap-3 border-b border-[#522C25]/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Thanh toán</p>
                    <h2 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Đơn #{{ $order->ma_order }}</h2>
                    <p class="mt-1 text-sm text-[#522C25]/60">{{ $order->ban ? 'Bàn ' . $order->ban->so_ban : 'Mang về' }} · {{ $order->chiTietOrders->sum('so_luong') }} món</p>
                </div>
                <div class="rounded-xl bg-[#FFF7E8] px-4 py-3 text-left sm:text-right">
                    <p class="text-xs font-medium text-[#8B5A2B]">Tổng cần thu</p>
                    <p class="text-2xl font-bold text-[#8B5A2B]">{{ number_format($tongTien, 0, ',', '.') }}đ</p>
                </div>
            </div>

            <div class="mt-4 max-h-[260px] space-y-2 overflow-y-auto pr-1">
                @foreach($order->chiTietOrders as $item)
                <div class="rounded-xl border border-[#522C25]/10 bg-[#FAF7F2] p-3 transition hover:border-[#8B5A2B]/30">
                    <div class="flex items-start justify-between gap-4 text-sm">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[#1A1A1A]">{{ $item->mon->ten_mon }} x{{ $item->so_luong }}</p>
                            @if($item->ghi_chu)
                                <p class="mt-1 text-xs text-[#522C25]/55">{{ $item->ghi_chu }}</p>
                            @endif
                        </div>
                        <p class="shrink-0 font-semibold text-[#522C25]">{{ number_format(($item->don_gia_tai_thoi_diem + $item->options->sum('gia_them')) * $item->so_luong, 0, ',', '.') }}đ</p>
                    </div>

                    @if($item->so_luong > 1)
                    <form action="{{ route('orders.split', $order->ma_order) }}" method="POST" class="mt-3 grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end">
                        @csrf
                        <input type="hidden" name="ma_mon" value="{{ $item->ma_mon }}">
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold text-[#522C25]/60">Tách số lượng</label>
                            <input type="number" name="so_luong_tach" min="1" max="{{ $item->so_luong - 1 }}"
                                   class="w-full rounded-lg border border-[#522C25]/15 px-3 py-2 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                        </div>
                        <button class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#8B5A2B] ring-1 ring-[#8B5A2B]/20 transition hover:bg-[#FFF7E8]">
                            Tách đơn
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="mt-4 rounded-xl border border-[#522C25]/10 bg-[#FAF7F2] p-3">
                <form action="{{ route('orders.merge', $order->ma_order) }}" method="POST" class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end">
                    @csrf
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-[#522C25]/60">Gộp đơn khác vào đơn này</label>
                        <select name="target_order" class="w-full rounded-lg border border-[#522C25]/15 px-3 py-2 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                            <option value="">Chọn đơn cần gộp</option>
                            @foreach($mergeTargets as $target)
                                <option value="{{ $target->ma_order }}">
                                    {{ $target->ma_order }} - {{ $target->ban ? 'Bàn ' . $target->ban->so_ban : 'Mang về' }} - {{ $target->trang_thai }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-lg bg-[#1A1A1A] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#522C25]">
                        Gộp đơn
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('payment.process', $order->ma_order) }}" class="mt-4 border-t border-[#522C25]/10 pt-4">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#522C25]/65">Chiết khấu (%)</label>
                        <input type="number" name="chiet_khau" value="0" min="0" max="100" step="1"
                               class="w-full rounded-xl border border-[#522C25]/15 px-4 py-2.5 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#522C25]/65">Phương thức thanh toán</label>
                        <select name="phuong_thuc_tt" id="payment-method" class="w-full rounded-xl border border-[#522C25]/15 px-4 py-2.5 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                            <option value="tien_mat">Tiền mặt</option>
                            <option value="chuyen_khoan">Chuyển khoản</option>
                            <option value="momo">MoMo</option>
                            <option value="vnpay">VNPay</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full rounded-xl bg-[#8B5A2B] py-3 text-sm font-semibold text-white shadow-lg shadow-[#8B5A2B]/20 transition hover:bg-[#6F4621]">
                    Xác nhận thanh toán
                </button>
            </form>
        </section>

        <aside id="qr-panel" class="hidden rounded-2xl border border-[#522C25]/10 bg-white p-5 shadow-[0_18px_50px_rgba(82,44,37,0.08)] lg:sticky lg:top-6">
            <p class="text-sm font-semibold text-[#1A1A1A]">Mã QR thanh toán</p>
            <p class="mt-1 text-xs text-[#522C25]/60" id="qr-copy">Quét mã để thanh toán đơn #{{ $order->ma_order }}.</p>

            <div class="mt-5 grid place-items-center rounded-2xl bg-[#FAF7F2] p-6">
                <div id="bank-qr" class="hidden rounded-xl bg-white p-4 shadow-sm">
                    {!! QrCode::format('svg')->size(220)->margin(1)->generate($bankPayload) !!}
                </div>
                <div id="momo-qr" class="hidden rounded-xl bg-white p-4 shadow-sm">
                    {!! QrCode::format('svg')->size(220)->margin(1)->generate($momoPayload) !!}
                </div>
            </div>

            <div class="mt-5 space-y-2 rounded-2xl bg-[#FFF7E8] p-4 text-sm text-[#522C25]">
                <p><span class="font-semibold">Nội dung:</span> THANH TOAN {{ $order->ma_order }}</p>
                <p><span class="font-semibold">Số tiền:</span> {{ number_format($tongTien, 0, ',', '.') }}đ</p>
            </div>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const layout = document.getElementById('payment-layout');
    const method = document.getElementById('payment-method');
    const panel = document.getElementById('qr-panel');
    const bankQr = document.getElementById('bank-qr');
    const momoQr = document.getElementById('momo-qr');
    const copy = document.getElementById('qr-copy');

    const syncQr = () => {
        const value = method.value;
        const showBank = value === 'chuyen_khoan' || value === 'vnpay';
        const showMomo = value === 'momo';
        const hasQr = showBank || showMomo;

        panel.classList.toggle('hidden', !hasQr);
        bankQr.classList.toggle('hidden', !showBank);
        momoQr.classList.toggle('hidden', !showMomo);

        layout.classList.toggle('max-w-xl', !hasQr);
        layout.classList.toggle('max-w-6xl', hasQr);
        layout.classList.toggle('lg:grid-cols-[minmax(0,1fr)_380px]', hasQr);

        copy.textContent = showMomo
            ? 'Quét mã MoMo để thanh toán đơn #{{ $order->ma_order }}.'
            : 'Quét mã chuyển khoản để thanh toán đơn #{{ $order->ma_order }}.';
    };

    method.addEventListener('change', syncQr);
    syncQr();
});
</script>
@endsection
