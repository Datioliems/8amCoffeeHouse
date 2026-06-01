@extends('layouts.app')
@section('page-title', 'Tạo đơn mang về')

@section('content')
<form method="POST" action="{{ route('orders.takeaway.store') }}" class="max-w-6xl space-y-5">
    @csrf

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10">
        <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
            <div>
                <label class="mb-1 block text-xs font-semibold text-[#522C25]/60">Tên khách</label>
                <input name="ten_kh" value="{{ old('ten_kh') }}" placeholder="Khách mang về"
                       class="w-full rounded-xl border border-[#522C25]/15 px-4 py-2.5 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-[#522C25]/60">Số điện thoại</label>
                <input name="sdt_kh" value="{{ old('sdt_kh') }}" placeholder="Không bắt buộc"
                       class="w-full rounded-xl border border-[#522C25]/15 px-4 py-2.5 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
            </div>
            <button class="rounded-xl bg-[#1A1A1A] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#522C25]">
                Tạo và thanh toán
            </button>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10">
        <div class="mb-4 border-b border-[#522C25]/10 pb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Takeaway order</p>
            <h2 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Chọn món cho khách mua mang về</h2>
            <p class="mt-1 text-sm text-[#522C25]/55">Bấm + / − để chọn số lượng từng món.</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach($mons as $index => $mon)
                <div x-data="{ qty: {{ (int) old("items.$index.so_luong", 0) }} }"
                     class="flex flex-col overflow-hidden rounded-2xl ring-1 transition"
                     :class="qty > 0 ? 'ring-2 ring-[#8B5A2B] bg-[#FFF7E8]' : 'ring-[#522C25]/10 bg-white'">
                    <input type="hidden" name="items[{{ $index }}][ma_mon]" value="{{ $mon->ma_mon }}">
                    <div class="aspect-[4/3] w-full overflow-hidden bg-[#F2F2F2]">
                        @if($mon->image_url)
                            <img src="{{ $mon->image_url }}" alt="{{ $mon->ten_mon }}" class="h-full w-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-3">
                        <p class="line-clamp-2 text-sm font-semibold text-[#1A1A1A]">{{ $mon->ten_mon }}</p>
                        <p class="mt-1 text-xs text-[#522C25]/55">{{ $mon->danhMuc->ten_danh_muc ?? 'Menu' }}</p>
                        <p class="mt-1 text-sm font-bold text-[#8B5A2B]">{{ number_format($mon->don_gia, 0, ',', '.') }}đ</p>

                        <div class="mt-3 flex items-center justify-between">
                            <button type="button" @click="qty = Math.max(0, qty - 1)"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F2F2F2] text-lg font-bold text-[#522C25]">−</button>
                            <input type="number" name="items[{{ $index }}][so_luong]" min="0" max="99" x-model.number="qty"
                                   class="w-12 rounded-lg border border-[#522C25]/15 px-1 py-1 text-center text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                            <button type="button" @click="qty = Math.min(99, qty + 1)"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-[#8B5A2B] text-lg font-bold text-white">+</button>
                        </div>
                        <input name="items[{{ $index }}][ghi_chu]" value="{{ old("items.$index.ghi_chu") }}" placeholder="Ghi chú"
                               x-show="qty > 0" x-cloak
                               class="mt-2 w-full rounded-lg border border-[#522C25]/15 px-2 py-1.5 text-xs focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</form>
@endsection
