@extends('layouts.app')
@section('page-title', 'Bàn ' . $ban->so_ban . ' · Đặt món tại bàn')

@section('content')
<div class="max-w-6xl space-y-5">

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
    @endif

    {{-- Header bàn --}}
    <div class="flex flex-col gap-3 rounded-[2rem] bg-white p-5 ring-1 ring-[#522C25]/10 am-shadow sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Table</p>
            <h2 class="mt-1 text-2xl font-semibold">Bàn {{ $ban->so_ban }}
                <span class="text-sm font-normal text-[#522C25]/55">· {{ $ban->vi_tri ?: 'Khác' }} · {{ $ban->so_ghe ?? 4 }} ghế</span>
            </h2>
        </div>
        <a href="{{ route('orders.index') }}" class="self-start rounded-full bg-[#F2F2F2] px-4 py-2 text-sm font-semibold text-[#522C25] hover:bg-[#E9DDD0]">← Về order board</a>
    </div>

    {{-- Đơn đang mở tại bàn --}}
    @if($activeOrders->count())
    <section class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Đơn đang mở tại bàn ({{ $activeOrders->count() }})</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($activeOrders as $o)
            @php
                $tong = (int) $o->chiTietOrders->sum(fn($i) => ($i->don_gia_tai_thoi_diem + $i->options->sum('gia_them')) * $i->so_luong);
            @endphp
            <div class="rounded-2xl border border-[#522C25]/10 bg-[#FAF7F2] p-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-xs font-semibold text-[#1A1A1A]">#{{ $o->ma_order }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $o->hinh_thuc==='mang_ve' ? 'bg-[#FFE3D6] text-[#9a3412]' : 'bg-[#E8F0DD] text-[#3f5325]' }}">
                        {{ $o->dung_coc_nhua ? '🥤 Mang về' : '🍵 Tại bàn' }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-[#522C25]/65">{{ $o->chiTietOrders->sum('so_luong') }} món · {{ \App\Services\OrderService::statusLabel($o->trang_thai) }}</p>
                <p class="mt-1 text-lg font-bold text-[#8B5A2B]">{{ number_format($tong, 0, ',', '.') }}đ</p>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('orders.show', $o->ma_order) }}" class="flex-1 rounded-full bg-[#1A1A1A] py-2 text-center text-xs font-semibold text-white">Chi tiết</a>
                    @if(in_array($o->trang_thai, ['da_xac_nhan','dang_pha_che','da_phuc_vu']))
                    <a href="{{ route('payment.show', $o->ma_order) }}" class="flex-1 rounded-full bg-[#52613B] py-2 text-center text-xs font-semibold text-white">Thanh toán</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Form đặt món mới cho bàn --}}
    <form method="POST" action="{{ route('orders.table.store', $ban->ma_ban) }}" class="space-y-5">
        @csrf

        <section class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10">
            <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-[#522C25]/60">Tên khách (không bắt buộc)</label>
                    <input name="ten_kh" value="{{ old('ten_kh') }}" placeholder="Khách tại bàn {{ $ban->so_ban }}"
                           class="w-full rounded-xl border border-[#522C25]/15 px-4 py-2.5 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                </div>
                <button class="rounded-xl bg-[#1A1A1A] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#522C25]">
                    Tạo đơn cho bàn
                </button>
            </div>

            {{-- Hình thức phục vụ --}}
            <div class="mt-4" x-data="{ ht: '{{ old('hinh_thuc', 'tai_ban') }}' }">
                <p class="mb-2 text-xs font-semibold text-[#522C25]/60">Hình thức phục vụ</p>
                <div class="grid grid-cols-2 gap-3 sm:max-w-md">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm transition"
                           :class="ht==='tai_ban' ? 'border-[#52613B] bg-[#E8F0DD]' : 'border-[#522C25]/15 bg-white'">
                        <input type="radio" name="hinh_thuc" value="tai_ban" x-model="ht" class="text-[#52613B]">
                        🍵 Uống tại bàn
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm transition"
                           :class="ht==='mang_ve' ? 'border-[#9a3412] bg-[#FFE3D6]' : 'border-[#522C25]/15 bg-white'">
                        <input type="radio" name="hinh_thuc" value="mang_ve" x-model="ht" class="text-[#9a3412]">
                        🥤 Mang về (cốc nhựa)
                    </label>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10">
            <div class="mb-4 border-b border-[#522C25]/10 pb-4">
                <h2 class="text-lg font-semibold text-[#1A1A1A]">Chọn món</h2>
                <p class="mt-1 text-sm text-[#522C25]/55">Bấm + / − để chọn số lượng; “+ Topping” ở góc thẻ để thêm topping.</p>
            </div>

            @php $gi = 0; $monsByCat = $mons->groupBy(fn($m) => $m->danhMuc->ten_danh_muc ?? 'Khác'); @endphp

            @foreach($monsByCat as $cat => $catMons)
            <div class="mb-6">
                <h3 class="mb-3 text-sm font-bold text-[#522C25]/70">{{ $cat }} <span class="text-[#522C25]/40">({{ $catMons->count() }})</span></h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($catMons as $mon)
                    @php $index = $gi++; @endphp
                    <div x-data="{ qty: {{ (int) old("items.$index.so_luong", 0) }}, showTop: false, tops: {} }"
                         class="relative flex flex-col overflow-hidden rounded-2xl ring-1 transition"
                         :class="qty > 0 ? 'ring-2 ring-[#8B5A2B] bg-[#FFF7E8]' : 'ring-[#522C25]/10 bg-white'">
                        <input type="hidden" name="items[{{ $index }}][ma_mon]" value="{{ $mon->ma_mon }}">

                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#F2F2F2]">
                            @if($mon->image_url)
                                <img src="{{ $mon->image_url }}" alt="{{ $mon->ten_mon }}" class="h-full w-full object-cover" loading="lazy">
                            @endif
                            @if($toppings->count())
                            <button type="button" @click="showTop = !showTop"
                                    class="absolute right-2 top-2 rounded-full bg-black/65 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur hover:bg-black/80">
                                + Topping
                                <span x-show="Object.values(tops).filter(Boolean).length" x-cloak
                                      class="ml-1 rounded-full bg-[#E82C2A] px-1.5" x-text="Object.values(tops).filter(Boolean).length"></span>
                            </button>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-3">
                            <p class="line-clamp-2 text-sm font-semibold text-[#1A1A1A]">{{ $mon->ten_mon }}</p>
                            <p class="mt-1 text-sm font-bold text-[#8B5A2B]">{{ number_format($mon->don_gia, 0, ',', '.') }}đ</p>

                            <div class="mt-3 flex items-center justify-between">
                                <button type="button" @click="qty = Math.max(0, qty - 1)"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F2F2F2] text-lg font-bold text-[#522C25]">−</button>
                                <input type="number" name="items[{{ $index }}][so_luong]" min="0" max="99" x-model.number="qty"
                                       class="w-12 rounded-lg border border-[#522C25]/15 px-1 py-1 text-center text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                                <button type="button" @click="qty = Math.min(99, qty + 1)"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-[#8B5A2B] text-lg font-bold text-white">+</button>
                            </div>

                            @if($toppings->count())
                            <div x-show="showTop" x-cloak class="mt-2 space-y-1 rounded-lg bg-[#F8F6F5] p-2">
                                @foreach($toppings as $k => $top)
                                <label class="flex items-center justify-between gap-2 text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <input type="checkbox" x-model="tops[{{ $k }}]" class="rounded border-[#522C25]/30 text-[#8B5A2B]">
                                        {{ $top->ten_topping }}
                                    </span>
                                    <span class="text-[#522C25]/55">+{{ number_format($top->gia_them, 0, ',', '.') }}đ</span>
                                </label>
                                <input type="hidden" :disabled="!tops[{{ $k }}]" name="items[{{ $index }}][options][{{ $k }}][type]"  value="topping">
                                <input type="hidden" :disabled="!tops[{{ $k }}]" name="items[{{ $index }}][options][{{ $k }}][value]" value="{{ $top->ten_topping }}">
                                <input type="hidden" :disabled="!tops[{{ $k }}]" name="items[{{ $index }}][options][{{ $k }}][price]" value="{{ (int) $top->gia_them }}">
                                @endforeach
                            </div>
                            @endif

                            <input name="items[{{ $index }}][ghi_chu]" value="{{ old("items.$index.ghi_chu") }}" placeholder="Ghi chú"
                                   x-show="qty > 0" x-cloak
                                   class="mt-2 w-full rounded-lg border border-[#522C25]/15 px-2 py-1.5 text-xs focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </section>
    </form>
</div>
@endsection
