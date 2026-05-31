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

    <section class="overflow-hidden rounded-2xl bg-white ring-1 ring-[#522C25]/10">
        <div class="border-b border-[#522C25]/10 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Takeaway order</p>
            <h2 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Chọn món cho khách mua mang về</h2>
        </div>

        <div class="divide-y divide-[#522C25]/10">
            @foreach($mons as $index => $mon)
                <div class="grid gap-3 p-4 md:grid-cols-[1fr_120px_220px] md:items-center">
                    <input type="hidden" name="items[{{ $index }}][ma_mon]" value="{{ $mon->ma_mon }}">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#1A1A1A]">{{ $mon->ten_mon }}</p>
                        <p class="mt-1 text-sm text-[#522C25]/60">
                            {{ $mon->danhMuc->ten_danh_muc ?? 'Menu' }} · {{ number_format($mon->don_gia, 0, ',', '.') }}đ
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#522C25]/50 md:hidden">Số lượng</label>
                        <input type="number" name="items[{{ $index }}][so_luong]" min="0" max="99" value="{{ old("items.$index.so_luong", 0) }}"
                               class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-center text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#522C25]/50 md:hidden">Ghi chú</label>
                        <input name="items[{{ $index }}][ghi_chu]" value="{{ old("items.$index.ghi_chu") }}" placeholder="Ghi chú món"
                               class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm focus:border-[#8B5A2B] focus:ring-[#8B5A2B]">
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</form>
@endsection
